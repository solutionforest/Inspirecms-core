<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;

use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Actions\QuickCreateAction;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;

class ListDocumentTypes extends ListRecords
{
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

    protected function configureQuickCreateAction(QuickCreateAction $action): void
    {
        $resource = static::getResource();

        // Check 'quickForm' method exists
        if (!method_exists($resource, 'quickForm')) {
            throw new \Exception('quickForm method not found in ' . $resource);
        }

        $action
            ->authorize($resource::canCreate())
            ->model($this->getModel())
            ->modelLabel(strtolower($this->getModelLabel() ?? $resource::getModelLabel()))
            ->form(fn (Form $form) => $resource::quickForm($form->columns(1)))
            ->color('info')
            ->canCreateAnother(false);
    }

    protected function configureCloneAction(CloneAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canCreate())
            ->model($this->getModel())
            ->modelLabel($this->getModelLabel() ?? static::getResource()::getModelLabel());
    }

    protected function configureQuickEditAction(QuickEditAction $action): void
    {
        $resource = static::getResource();
        
        // Check 'quickForm' method exists
        if (!method_exists($resource, 'quickForm')) {
            throw new \Exception('quickForm method not found in ' . $resource);
        }

        $action
            ->authorize(fn (Model $record): bool => $resource::canEdit($record))
            ->form(fn (Form $form): Form => $resource::quickForm($form->columns(1)));
    }
}
