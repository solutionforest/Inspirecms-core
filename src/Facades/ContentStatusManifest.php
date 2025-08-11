<?php

namespace SolutionForest\InspireCms\Facades;

use Filament\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifestInterface;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;

/**
 * @method static void addOption(ContentStatusOption $option, bool $replace = false) Adds a new option to the manifest.
 * @method static void replaceOption(ContentStatusOption $option) Replaces an existing option in the manifest.
 * @method static null|ContentStatusOption getOption((int|string) $valueOrName) Retrieves an option by its value or name.Retrieves an option by its value or name.
 * @method static Collection selectOptions() Retrieves all available key/value select options from the manifest.
 * @method static Collection all() Retrieves all available options from the manifest.
 * @method static void setDefaultValue(int $value)
 * @method static null | int getDefaultValue()
 * @method static array<int, Action> getFormActions(array $excepts = [])
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
