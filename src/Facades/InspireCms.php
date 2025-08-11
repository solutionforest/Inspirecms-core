<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Dtos\NavigationDto;

/**
 * @method static ?stirng version()
 * @method static bool needInstall() Determine if there is a need to go to the install page
 * @method static ?string getInstallUrl()
 * @method static ?string getImportDataUrl()
 * @method static Collection<ClusterSection> getSections(...$names)
 * @method static void routes() Registers the routes for the Inspire CMS.
 * @method static void addSection(ClusterSection $section)
 * @method static array<string, LanguageDto> getAllAvailableLanguages() Get all available languages, indexed by their locale. (Default locale first)
 * @method static ?LanguageDto getFallbackLanguage()
 * @method static void forgetCachedLanguages()
 * @method static NavigationDto[] getNavigation(string $category, ?string $locale = null)
 * @method static void forgetCachedNavigation()
 * @method static array getContentRoutes()
 * @method static void forgetCachedContentRoutes()
 * @method static SeoDto getFallbackSeo()
 *
 * @see \SolutionForest\InspireCms\InspireCms
 */
class InspireCms extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \SolutionForest\InspireCms\InspireCms::class;
    }
}
