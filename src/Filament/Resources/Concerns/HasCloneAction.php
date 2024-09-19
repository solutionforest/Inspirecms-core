<?php

namespace SolutionForest\InspireCms\Filament\Resources\Concerns;

use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;

trait HasCloneAction
{
    protected function configureCloneAction(CloneAction $action): void
    {
        if ($this instanceof RelationManager) {
            
            $resource = $this->getPageClass()::getResource();
            $model = $this->getRelationship()->getModel();
            $modelLabel = $this->getTableModelLabel();

        } else {

            $resource = static::getResource();
            $model = $this->getModel();
            $modelLabel = $this->getModelLabel() ?? $resource::getModelLabel();
        }

        $action
            ->authorize(fn (Model $record): bool => $resource::can('replicate', $record))
            ->model($model)
            ->modelLabel($modelLabel);
    }
}
