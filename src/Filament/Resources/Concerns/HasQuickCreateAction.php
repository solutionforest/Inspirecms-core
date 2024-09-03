<?php

namespace SolutionForest\InspireCms\Filament\Resources\Concerns;

use Filament\Forms\Form;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Actions\QuickCreateAction;

trait HasQuickCreateAction
{
    protected function configureQuickCreateAction(QuickCreateAction $action): void
    {
        $resource = static::getResource();

        // Check 'quickForm' method exists
        if (! method_exists($resource, 'quickForm')) {
            throw new \Exception('quickForm method not found in ' . $resource);
        }

        $action
            ->authorize($resource::canCreate())
            ->model($this->getModel())
            ->modelLabel(strtolower($this->getModelLabel() ?? $resource::getModelLabel()))
            ->form(fn (Form $form) => $resource::quickForm($form->columns(1)))
            ->color('info')
            ->createAnother(false);
    }
}
