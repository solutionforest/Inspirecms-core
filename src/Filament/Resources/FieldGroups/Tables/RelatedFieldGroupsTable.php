<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Tables;

use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\FieldGroupForm;
use SolutionForest\InspireCms\Filament\Tables\Actions\OpenAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class RelatedFieldGroupsTable
{
    public static function fromDocumentType(Table $table): Table
    {
        return FieldGroupsTable::configure($table)
            ->reorderable('order')
            ->defaultSort('pivot_order')
            ->modifyQueryUsing(fn ($query) => $query->withCount('fields'))
            ->modelLabel(fn () => Str::lower(__('inspirecms::resources/document-type.field_groups.singular')))
            ->pluralModelLabel(fn () => Str::lower(__('inspirecms::resources/document-type.field_groups.plural')))
            ->description(fn () => __('inspirecms::resources/document-type.field_groups.description'))
            ->pushColumns([
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

                TextColumn::make('pivot.order')
                    ->label(__('inspirecms::inspirecms.order'))
                    ->sortable(),

            ])
            ->headerActions([

                CreateAction::make()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->after(function (RelationManager $livewire) {
                        $livewire->dispatch('refreshAlerts');
                    })
                    ->steps(FieldGroupForm::getStepsSchema())
                    ->skippableSteps(),

                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title', 'name'])
                    ->recordTitle(fn ($record) => UIHelper::generateTextWithDescription($record->title, $record->name)->toHtml())
                    ->recordSelect(
                        fn (Select $select) => $select
                            ->searchable()
                            ->allowHtml()
                    )
                    ->multiple()
                    ->slideOver()
                    ->after(function (RelationManager $livewire) {
                        $livewire->dispatch('refreshAlerts');
                    }),
            ])
            ->recordActions([

                ViewAction::make()
                    ->iconButton()
                    ->modalDescription(fn ($record) => $record->name)
                    ->fillForm(fn ($record) => $record->fields->toArray())
                    ->slideOver()
                    ->modalWidth('5xl'),

                OpenAction::make()
                    ->url(function ($record) {
                        $resource = InspireCmsConfig::get('resources.field_group', FieldGroupResource::class);

                        return FilamentResourceHelper::attemptToGetUrl($resource, ['view', 'edit'], ['record' => $record], true);
                    }, true),

                DetachAction::make()
                    ->iconButton()
                    ->visible(fn ($record) => $record->pivot?->inheritedFrom == null)
                    ->after(function (RelationManager $livewire) {
                        $livewire->dispatch('refreshAlerts');
                    }),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->link()
                    ->after(function (RelationManager $livewire) {
                        $livewire->dispatch('refreshAlerts');
                    }),
            ]);
    }
}
