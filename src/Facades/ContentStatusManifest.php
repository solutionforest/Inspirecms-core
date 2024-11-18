<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifestInterface;

/**
 * @method static void addOption(\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption $option, bool $replace = false) Adds a new option to the manifest.
 * @method static void replaceOption(\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption $option) Replaces an existing option in the manifest.
 * @method static null|\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption getOption(int | string $valueOrName) Retrieves an option by its value or name.Retrieves an option by its value or name.
 * @method static \Illuminate\Support\Collection selectOptions() Retrieves all available key/value select options from the manifest.
 * @method static \Illuminate\Support\Collection all() Retrieves all available options from the manifest.
 * @method static void setDefaultValue(int $value)
 * @method static null | int getDefaultValue()
 *
 * @see \SolutionForest\InspireCms\Base\Manifests\ContentStatusManifest
 */
class ContentStatusManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ContentStatusManifestInterface::class;
    }
}
