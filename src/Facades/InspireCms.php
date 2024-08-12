<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SolutionForest\InspireCms\InspireCms
 */
class InspireCms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SolutionForest\InspireCms\InspireCmsManager::class;
    }
}
