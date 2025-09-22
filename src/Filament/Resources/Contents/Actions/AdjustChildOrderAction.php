<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Actions;

use SolutionForest\InspireCms\Filament\Actions\ReorderContentAction;

class AdjustChildOrderAction
{
    public static function make(): ReorderContentAction
    {
        return ReorderContentAction::make();
    }
}
