<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Wireable;

abstract class BaseFilter implements Wireable
{
    /**
     * @param Builder $query
     * @return Builder
     */
    public abstract function applyToQuery($query);
}
