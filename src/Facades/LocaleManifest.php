<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\LocaleManifestInterface;

/**
 * @method static void addLocale(string $Locale)
 * @method static array getLocales()
 * @method static array selectOptions(?string $displayLocale = null)
 * 
 * @see \SolutionForest\InspireCms\Base\Manifests\LocaleManifest
 */
class LocaleManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return LocaleManifestInterface::class;
    }
}
