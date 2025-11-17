<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Tables;

use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Tables\Columns\BladeIconColumn;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypesAssociationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function () use ($table) {
                $arguments = $table->getArguments();
                $parentDocumentTypeId = $arguments['parentDocumentTypeId'] ?? null;

                $query = InspireCmsConfig::getDocumentTypeModelClass()::query()
                    ->whereCanBeContent();

                if ($parentDocumentTypeId !== null) {
                    $query
                        // Skip self
                        ->whereKeyNot($parentDocumentTypeId)
                        ->whereHas(
                            'allowingDocumentTypes',
                            fn ($query) => $query->whereKey($parentDocumentTypeId)
                        );
                }
                // Is root
                else {
                    $query->where('show_at_root', true);
                }

                return $query;
            })
            ->recordUrl(function ($record) use ($table) {
                $arguments = $table->getArguments();
                $parentContentId = $arguments['parentContentId'] ?? null;
                $translatableLocale = $arguments['translatableLocale'] ?? null;

                return \SolutionForest\InspireCms\Filament\Resources\Contents\Actions\CreateContentAction::generateCreateContentUrl(
                    documentType: $record,
                    parentContent: $parentContentId,
                    translatableLocale: $translatableLocale
                );
            })
            // Ensure checkbox for bulk actions is not shown
            ->toolbarActions([])
            ->disabledSelection()
            ->selectable(false)
            ->checkIfRecordIsSelectableUsing(fn ($record) => false)
            ->searchable()
            ->searchPlaceholder(__('inspirecms::inspirecms.search.placeholder'))
            ->emptyStateHeading(__('inspirecms::inspirecms.search.no_results'))
            ->columns([
                Split::make([
                    BladeIconColumn::make('icon')
                        ->grow(false)
                        ->extraAttributes([
                            'style' => 'width: 2rem;',
                        ]),
                    TextColumn::make('title')
                        ->description(fn ($record) => $record->slug ?? null),
                ]),
            ]);
    }
}
