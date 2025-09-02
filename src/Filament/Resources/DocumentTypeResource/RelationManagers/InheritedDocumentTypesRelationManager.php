<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
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

        if ($ownerRecord instanceof DocumentType) {
            return $ownerRecord->display_category?->canInheriting() ?? false;
        }

        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->description(fn () => __('inspirecms::resources/document-type.inherited.description'))
            ->columns([
                TextColumn::make('title')
                    ->label(__('inspirecms::resources/document-type.title.label')),
                TextColumn::make('slug')
                    ->label(__('inspirecms::resources/document-type.slug.label'))
                    ->badge(),
            ])
            ->recordUrl(fn ($record) => $this->getRecordUrl($record))
            ->openRecordUrlInNewTab()
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn ($query) => $query->canBeInherited()),
            ])
            ->recordActions([
                DetachAction::make()
                    ->iconButton(),
            ]);
    }

    protected function getRecordUrl(DocumentType $record): ?string
    {
        $resource = InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class);

        return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], ['record' => $record], true);
    }

    protected function configureAttachAction(AttachAction $action): void
    {
        parent::configureAttachAction($action);

        $action
            ->slideOver()
            ->modalWidth('lg')
            ->after(function (array $data) {
                $recordId = $data['recordId'] ?? null;

                if (! $recordId) {
                    return;
                }

                $success = $this->getOwnerRecord()->inheritFieldGroupsFrom($recordId);

                $this->dispatch('refreshFieldGroups');
                $this->dispatch('refreshAlerts');
            });
    }

    protected function configureDetachAction(DetachAction $action): void
    {
        parent::configureDetachAction($action);

        $action->after(function (?Model $record) {
            if (! $record) {
                return;
            }

            $this->getOwnerRecord()->deteachInheritFieldGroupsFrom($record);

            $this->dispatch('refreshFieldGroups');
            $this->dispatch('refreshAlerts');
        });
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::resources/document-type.inherited.label', [
            'name' => static::getModelLabel(),
        ]);
    }

    protected static function getModelLabel(): ?string
    {
        return Str::lower(__('inspirecms::inspirecms.document_type.singular'));
    }
}
