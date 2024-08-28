<?php

namespace SolutionForest\InspireCms\Filament\Resources\Concerns;

use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;

trait HasCloneAction
{
    protected function configureCloneAction(CloneAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canCreate())
            ->model($this->getModel())
            ->modelLabel($this->getModelLabel() ?? static::getResource()::getModelLabel());
    }
}
