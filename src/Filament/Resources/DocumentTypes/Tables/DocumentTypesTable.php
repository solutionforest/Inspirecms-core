<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Schemas\DocumentTypeForm;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Tables\Filters\DocumentTypeCategoryFilter;
use SolutionForest\InspireCms\Filament\Tables\Columns\BladeIconColumn;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class DocumentTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('inspirecms::resources/document-type.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/document-type.empty_state.description'))
            ->emptyStateActions([])
            ->columns([
                TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                BladeIconColumn::make('icon')
                    ->label(__('inspirecms::resources/document-type.icon.label'))
                    ->width('1%'),
                TextColumn::make('title')
                    ->label(__('inspirecms::resources/document-type.title.label')),
                TextColumn::make('slug')
                    ->label(__('inspirecms::resources/document-type.slug.label'))
                    ->sortable()
                    ->badge(),
                IconColumn::make('show_as_table')
                    ->label(__('inspirecms::resources/document-type.show_as_table.label'))
                    ->boolean(),
                IconColumn::make('show_at_root')
                    ->label(__('inspirecms::resources/document-type.show_at_root.label'))
                    ->boolean(),
                TextColumn::make('display_category')
                    ->label(__('inspirecms::resources/document-type.category.label'))
                    ->badge(),

                // timestamps
                TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
                TextColumn::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                ReplicateAction::make()
                    ->iconButton()
                    ->schema(fn (Schema $schema) => DocumentTypeForm::configure($schema))
                    ->excludeAttributes(['templates_count', 'field_groups_count', 'children_count'])
                    ->after(function (Model | DocumentType $replica, Model | DocumentType $record) {

                        $fieldGroups = $record->fieldGroups()->pluck($record->fieldGroups()->getQualifiedRelatedKeyName())->toArray();

                        $replica->fieldGroups()->sync($fieldGroups);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            ->checkIfRecordIsSelectableUsing(function (DocumentType | Model $record) use ($table) {

                $arguments = $table->getArguments();

                $isFromModalTableSelect = data_get($arguments, 'fromModalTableSelect') === true;
                $fromDocumentType = data_get($arguments, 'fromDocumentType');

                // Logic for when the table is opened from a modal
                if ($isFromModalTableSelect) {
                    // Avoid select the record itself
                    $fromDocumentTypeId = $fromDocumentType instanceof Model ? $fromDocumentType->getKey() : $fromDocumentType;
                    if ($fromDocumentType && $record->getKey() === $fromDocumentTypeId) {
                        return false;
                    }

                    return true;
                }

                $hasContent = $record->content()->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ])->count() > 0;
                if ($hasContent) {
                    // Disallow delete this document type if have content
                    return false;
                }

                return true;
            })
            ->filters([
                TernaryFilter::make('show_as_table')
                    ->label(__('inspirecms::resources/document-type.show_as_table.label')),
                TernaryFilter::make('show_at_root')
                    ->label(__('inspirecms::resources/document-type.show_at_root.label')),
                DocumentTypeCategoryFilter::make(),
            ]);
    }
}
