<?php

namespace SolutionForest\InspireCms\Filament\Resources\Concerns;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;

trait HasQuickEditAction
{
    protected function configureQuickEditAction(QuickEditAction $action): void
    {
        if ($this instanceof RelationManager) {
            
            $resource = $this->getPageClass()::getResource();

        } else {

            $resource = static::getResource();
            
        }

        // Check 'quickForm' method exists
        if (! method_exists($resource, 'quickForm')) {
            throw new \Exception('quickForm method not found in ' . $resource);
        }

        $action
            ->authorize(fn (Model $record): bool => $resource::canEdit($record))
            ->form(fn (Form $form): Form => $resource::quickForm($form->columns(1)));
    }
}
