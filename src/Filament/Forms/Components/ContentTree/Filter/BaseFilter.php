<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Wireable;

abstract class BaseFilter implements Wireable
{
    /**
     * @param  Builder  $query
     * @return Builder
     */
    abstract public function applyToQuery($query);

    public function toLivewire()
    {
        return [
            '__fqcn' => static::class,
        ];
    }
}
