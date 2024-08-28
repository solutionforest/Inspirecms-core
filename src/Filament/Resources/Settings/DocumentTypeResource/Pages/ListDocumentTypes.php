<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use SolutionForest\InspireCms\Filament\Actions\QuickCreateAction;
use SolutionForest\InspireCms\Filament\Resources\Concerns\HasCloneAction;
use SolutionForest\InspireCms\Filament\Resources\Concerns\HasQuickCreateAction;
use SolutionForest\InspireCms\Filament\Resources\Concerns\HasQuickEditAction;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;

class ListDocumentTypes extends ListRecords
{
    use HasCloneAction;
    use HasQuickCreateAction;
    use HasQuickEditAction;

    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            QuickCreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.document_type', DocumentTypeResource::class);
    }

    protected function configureAction(Actions\Action $action): void
    {
        match (true) {
            $action instanceof QuickCreateAction => $this->configureQuickCreateAction($action),
            default => parent::configureAction($action),
        };
    }

    protected function configureTableAction(\Filament\Tables\Actions\Action $action): void
    {
        match (true) {
            $action instanceof CloneAction => $this->configureCloneAction($action),
            $action instanceof QuickEditAction => $this->configureQuickEditAction($action),
            default => parent::configureTableAction($action),
        };
    }
}
