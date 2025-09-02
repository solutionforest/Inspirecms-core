<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Tables\Actions\OpenAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class AllowingDocumentTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'allowingDocumentTypes';

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('inspirecms::inspirecms.document_type.singular'))
            ->pluralModelLabel(__('inspirecms::inspirecms.document_type.plural'))
            ->columns([
                TextColumn::make('title')
                    ->label(__('inspirecms::resources/document-type.title.label')),
                TextColumn::make('slug')
                    ->label(__('inspirecms::resources/document-type.slug.label'))
                    ->badge(),
            ])
            ->recordUrl(fn ($record) => $this->getRecordUrl($record))
            ->openRecordUrlInNewTab()
            ->description(fn () => __('inspirecms::resources/document-type.allowing_document_types.description'))
            ->recordActions([
                OpenAction::make()
                    ->url(fn ($record) => $this->getRecordUrl($record)),
            ]);
    }

    protected function getRecordUrl(DocumentType $record): ?string
    {
        $resource = InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class);

        return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], ['record' => $record], true);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::resources/document-type.allowing_document_types.label');
    }
}
