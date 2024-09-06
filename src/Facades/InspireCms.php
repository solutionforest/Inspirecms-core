<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool needInstall() Determine if there is a need to go to the install page
 * @method static ?string getInstallUrl()
 * @method static \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection> getSections(... $names)
 * @method static void addSection(\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection $section)
 * @method static \Illuminate\Support\Collection<\SolutionForest\InspireCms\Models\Contracts\Language> getAllAvailableLanguages()
 *
 * @see \SolutionForest\InspireCms\InspireCmsManager
 */
class InspireCms extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \SolutionForest\InspireCms\InspireCmsManager::class;
    }
}
