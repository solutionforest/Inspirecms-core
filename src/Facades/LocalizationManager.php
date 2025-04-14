<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\LocaleManifestInterface;

/**
 * @method static void addUserPreferredLocale(string $locale)
 * @method static void removeUserPreferredLocale(string $locale)
 * @method static array getUserPreferredLocales()
 * @method static array getLocales()
 * @method static array getLocaleLabelsFor(array $locales, ?string $displayLocale = null)
 * @method static string getLocaleLabel(string $locale, ?string $displayLocale = null)
 *
 * @see \SolutionForest\InspireCms\Base\Manifests\LocaleManifest
 */
class LocalizationManager extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return LocaleManifestInterface::class;
    }
}
