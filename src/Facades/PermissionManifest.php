<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifestInterface;

/**
 * @see \SolutionForest\InspireCms\Base\Manifests\PermissionManifest
 */
class PermissionManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return PermissionManifestInterface::class;
    }
}
