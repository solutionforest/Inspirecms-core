<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool needInstall() Determine if there is a need to go to the install page
 * @method static ?string getInstallUrl()
 * 
 * @see \SolutionForest\InspireCms\InspireCms
 */
class InspireCms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SolutionForest\InspireCms\InspireCmsManager::class;
    }
}
