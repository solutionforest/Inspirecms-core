<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Forms\Form;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\CreateAction as ActionsCreateAction;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource\Pages\ManageFieldGroup as BasePage;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;

class ManageFieldGroup extends BasePage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.field_group', FieldGroupResource::class);
    }

    protected function configureTableAction(\Filament\Tables\Actions\Action $action): void
    {
        match (true) {
            $action instanceof CloneAction => $this->configureCloneAction($action),
            $action instanceof QuickEditAction => $this->configureQuickEditAction($action),
            default => parent::configureTableAction($action),
        };
    }

    protected function configureCreateAction(CreateAction|ActionsCreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action->modalFooterActionsAlignment(Alignment::End);
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
        if (! method_exists($resource, 'quickForm')) {
            throw new \Exception('quickForm method not found in ' . $resource);
        }

        $action
            ->authorize(fn (Model $record): bool => $resource::canEdit($record))
            ->form(fn (Form $form): Form => $resource::quickForm($form->columns(1)));
    }
}
