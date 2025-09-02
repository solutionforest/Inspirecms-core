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

class InheritingDocumentTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'inheritingDocumentTypes';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $ownerRecord->canBeInherited();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('inspirecms::resources/document-type.title.label')),
                TextColumn::make('slug')
                    ->label(__('inspirecms::resources/document-type.slug.label'))
                    ->badge(),
            ])
            ->recordUrl(fn ($record) => $this->getRecordUrl($record))
            ->openRecordUrlInNewTab()
            ->description(fn () => __('inspirecms::resources/document-type.inheriting.description'))
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
        return __('inspirecms::resources/document-type.inheriting.label', [
            'name' => static::getModelLabel(),
        ]);
    }

    protected static function getModelLabel(): ?string
    {
        return __('inspirecms::inspirecms.document_type.singular');
    }
}
