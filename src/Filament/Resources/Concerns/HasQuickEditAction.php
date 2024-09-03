<?php

namespace SolutionForest\InspireCms\Filament\Resources\Concerns;

use Filament\Forms\Form;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;

trait HasQuickEditAction
{
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
