<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class InheritedDocumentTypesRelationManager extends RelationManager
{
    use CanAuthorizeRelationManager;

    protected static string $relationship = 'inheritedDocumentTypes';

    protected static ?string $inverseRelationship = 'inheritingDocumentTypes';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $ownerRecord->canInheriting();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('inspirecms::inspirecms.slug'))
                    ->badge(),
            ])
            ->recordUrl(fn ($record) => $this->getRecordUrl($record))
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function ($query) {
                        $query->canBeInherited();
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->iconButton(),
            ]);
    }

    protected function getRecordUrl(DocumentType $record): ?string
    {
        $resource = config('inspirecms.filament.resources.document_type', DocumentTypeResource::class);

        return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], ['record' => $record], true);
    }

    protected function configureAttachAction(AttachAction $action): void
    {
        parent::configureAttachAction($action);

        $action->after(function (array $data) {
            $recordId = $data['recordId'] ?? null;

            if (! $recordId) {
                return;
            }

            $success = $this->getOwnerRecord()->inheritFieldGroupsFrom($recordId);

            $this->dispatch('refreshFieldGroups');
        });
    }

    protected function configureDetachAction(Tables\Actions\DetachAction $action): void
    {
        parent::configureDetachAction($action);

        $action->after(function (?Model $record) {
            if (! $record) {
                return;
            }

            $this->getOwnerRecord()->deteachInheritFieldGroupsFrom($record);

            $this->dispatch('refreshFieldGroups');
        });
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.inherited_xxx', [
            'name' => static::getModelLabel(),
        ]);
    }

    protected static function getModelLabel(): ?string
    {
        return __('inspirecms::inspirecms.document_type');
    }
}
