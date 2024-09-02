<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\ModelManifestInterface;

/**
 * @method static void register() Bind initial models to the container and establish explicit model bindings.
 * @method static void registerMorphMap() Register the morph map for polymorphic relations.
 * @method static void add(string $interfaceClass, string $modelClass) Register models.
 * @method static void replace(string $interfaceClass, string $modelClass) Replace a model with a different implementation.
 * @method static ?string get(string $interfaceClass, ?string $fallback = null) Gets the registered class for the interface.
 * 
 * @see \SolutionForest\InspireCms\Base\Manifests\ModelManifest
 */
class ModelManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ModelManifestInterface::class;
    }
}