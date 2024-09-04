<?php

namespace SolutionForest\InspireCms\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;

trait HasCloneAction
{
    protected function configureCloneAction(CloneAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize(fn (Model $record): bool => $resource::can('replicate', $record))
            ->model($this->getModel())
            ->modelLabel($this->getModelLabel() ?? static::getResource()::getModelLabel());
    }
}
