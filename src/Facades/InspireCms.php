<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool needInstall() Determine if there is a need to go to the install page
 * @method static ?string getInstallUrl()
 * @method static \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection> getSections(... $names)
 * @method static void routes() Registers the routes for the Inspire CMS.
 * @method static void addSection(\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection $section)
 * @method static array<string,\SolutionForest\InspireCms\Dtos\LanguageDto> getAllAvailableLanguages()
 * @method static ?\SolutionForest\InspireCms\Dtos\LanguageDto getFallbackLanguage()
 * @method static void forgetCachedLanguages()
 * @method static \SolutionForest\InspireCms\Dtos\NavigationDto[] getNavigation(string $category, ?string $locale = null)
 * @method static void forgetCachedNavigation()
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
