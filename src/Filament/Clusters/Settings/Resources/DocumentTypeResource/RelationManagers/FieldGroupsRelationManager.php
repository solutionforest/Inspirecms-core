<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class FieldGroupsRelationManager extends RelationManager
{
    use CanAuthorizeRelationManager;

    protected static string $relationship = 'fieldGroups';

    protected static ?string $inverseRelationship = 'documentTypes';

    protected $listeners = [
        'refreshFieldGroups' => '$refresh',
    ];

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('order')
            ->defaultSort('order')
            ->modifyQueryUsing(fn ($query) => $query->withCount('fields'))
            ->modelLabel(fn () => __('inspirecms::resources/document-type.field_groups.singular'))
            ->pluralModelLabel(fn () => __('inspirecms::resources/document-type.field_groups.plural'))
            ->description(fn () => __('inspirecms::resources/document-type.field_groups.description'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name'))
                    ->badge(),
                Tables\Columns\TextColumn::make('fields_count')
                    ->label(__('inspirecms::inspirecms.fields')),

                Tables\Columns\ColumnGroup::make('inherited_from')
                    ->label(__('inspirecms::resources/document-type.inherited_from.label'))
                    ->columns([

                        Tables\Columns\TextColumn::make('inherited_from_title')
                            ->label(__('inspirecms::inspirecms.title'))
                            ->getStateUsing(function ($record) {
                                return $record->pivot?->inheritedFrom?->title;
                            }),
                        Tables\Columns\TextColumn::make('inherited_from_slug')
                            ->label(__('inspirecms::resources/document-type.slug.label'))
                            ->width('5%')
                            ->getStateUsing(function ($record) {
                                return $record->pivot?->inheritedFrom?->slug;
                            })
                            ->badge(),
                    ]),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('inspirecms::inspirecms.order'))
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->url(function () {
                        $resource = InspireCmsConfig::get('resources.field_group', FieldGroupResource::class);

                        return FilamentResourceHelper::attemptToGetUrl($resource, 'create', [], true);
                    }, true)
                    ->visible(function (Tables\Actions\Action $action) {
                        return filled($action->getUrl());
                    }),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title', 'name']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->modalDescription(fn ($record) => $record->name)
                    ->fillForm(fn ($record) => $record->fields->toArray())
                    ->slideOver()
                    ->modalWidth('5xl'),
                Tables\Actions\Action::make('open')
                    ->label(__('inspirecms::actions.open.label'))
                    ->icon(FilamentIcon::resolve('inspirecms::goto'))
                    ->iconPosition(IconPosition::After)
                    ->url(function ($record) {
                        $resource = InspireCmsConfig::get('resources.field_group', FieldGroupResource::class);

                        return FilamentResourceHelper::attemptToGetUrl($resource, ['view', 'edit'], ['record' => $record], true);
                    }, true)
                    ->visible(fn (Tables\Actions\Action $action) => filled($action->getUrl())),
                Tables\Actions\DetachAction::make()
                    ->iconButton()
                    ->visible(fn ($record) => $record->pivot?->inheritedFrom == null),
            ])
            ->actionsAlignment('left');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\RepeatableEntry::make('fields')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->columns(6)
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->columnSpan(1)
                            ->hiddenLabel()
                            ->getStateUsing(fn ($record) => $record->field_type_config[0]['name'] ?? null)
                            ->icon(fn ($record) => $record->field_type_config[0]['icon'] ?? 'heroicon-o-minus-circle'),
                        Infolists\Components\Group::make([
                            Infolists\Components\TextEntry::make('label')
                                ->label(__('inspirecms::resources/field.label.label'))
                                ->inlineLabel(),

                            Infolists\Components\TextEntry::make('name')
                                ->label(__('inspirecms::resources/field.name.label'))
                                ->inlineLabel()
                                ->badge(),

                        ])->columnSpan(5),
                    ]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::resources/document-type.field_groups.label');
    }
}
