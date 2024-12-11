<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard\Step;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Filament\Resources\Helpers\FieldGroupResourceHelper;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Field;

class FieldGroupsRelationManager extends RelationManager
{
    use CanAuthorizeRelationManager;

    protected static string $relationship = 'fieldGroups';

    protected static ?string $inverseRelationship = 'documentTypes';

    protected $listeners = [
        'refreshFieldGroups' => '$refresh',
    ];

    protected static function getStepSchema()
    {
        return [
            Step::make('fields')
                ->label(__('inspirecms::resources/field-group.steps.fields.label'))
                ->schema([
                    FieldGroupResourceHelper::getFieldsRepeater()->hiddenLabel(),
                ]),
            Step::make('settings')
                ->label(__('inspirecms::resources/field-group.steps.settings.label'))
                ->schema([
                    FieldGroupResourceHelper::getNameFormComponent(),
                    FieldGroupResourceHelper::getTitleFormComponent(),
                    FieldGroupResourceHelper::getActiveFormComponent()->hidden()->dehydratedWhenHidden()->dehydrateStateUsing(fn () => true),
                ]),
        ];
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

                            Infolists\Components\IconEntry::make('translatable')
                                ->label(__('inspirecms::resources/field.translatable.label'))
                                ->inlineLabel()
                                ->getStateUsing(fn (Model | Field $record) => Arr::get($record->config ?? [], 'translatable', false) === true)
                                ->boolean()
                                ->falseColor('gray'),

                        ])->columnSpan(5),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('order')
            ->defaultSort('pivot_order')
            ->modifyQueryUsing(fn ($query) => $query->withCount('fields'))
            ->modelLabel(fn () => Str::lower(__('inspirecms::resources/document-type.field_groups.singular')))
            ->pluralModelLabel(fn () => Str::lower(__('inspirecms::resources/document-type.field_groups.plural')))
            ->description(fn () => __('inspirecms::resources/document-type.field_groups.description'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::resources/field-group.title.label')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::resources/field-group.name.label'))
                    ->badge(),
                Tables\Columns\TextColumn::make('fields_count')
                    ->label(__('inspirecms::resources/field-group.fields.label')),

                // Tables\Columns\ColumnGroup::make('inherited_from')
                //     ->label(__('inspirecms::resources/document-type.inherited_from.label'))
                //     ->columns([

                //         Tables\Columns\TextColumn::make('inherited_from_title')
                //             ->label(__('inspirecms::inspirecms.title'))
                //             ->getStateUsing(function ($record) {
                //                 return $record->pivot?->inheritedFrom?->title;
                //             }),
                //         Tables\Columns\TextColumn::make('inherited_from_slug')
                //             ->label(__('inspirecms::resources/document-type.slug.label'))
                //             ->width('5%')
                //             ->getStateUsing(function ($record) {
                //                 return $record->pivot?->inheritedFrom?->slug;
                //             })
                //             ->badge(),
                //     ]),

                Tables\Columns\TextColumn::make('pivot.order')
                    ->label(__('inspirecms::inspirecms.order'))
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title', 'name'])
                    ->recordTitle(fn ($record) => UIHelper::generateTextWithBadge($record->title, $record->name)->toHtml())
                    ->recordSelect(
                        fn (Select $select) => $select
                            ->searchable()
                            ->allowHtml()
                    ),
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
            ->bulkActions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::resources/document-type.field_groups.label');
    }

    protected function configureCreateAction(CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action
            ->slideOver()
            ->modalWidth('7xl')
            ->after(function (Model $record) {
                $this->dispatch('refreshAlerts');
            })
            ->steps(static::getStepSchema())
            ->skippableSteps();
    }

    protected function configureAttachAction(Tables\Actions\AttachAction $action): void
    {
        parent::configureAttachAction($action);

        $action
            ->multiple()
            ->slideOver()
            ->modalWidth('lg')
            ->after(function (array $data) {
                $this->dispatch('refreshAlerts');
            });
    }

    protected function configureDetachAction(Tables\Actions\DetachAction $action): void
    {
        parent::configureDetachAction($action);

        $action->after(function (array $data) {
            $this->dispatch('refreshAlerts');
        });
    }
}
