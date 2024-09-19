<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifestInterface;

/**
 * @method static string getSuperAdminRoleName()
 * @method static void setSuperAdminRoleName(string $name)
 * @method static \Illuminate\Support\Collection<string> permissions()
 * @method static array getClusterSectionPermissions()
 * @method static array getClusterSectionResourceModelPermissions()
 * @method static string getPermissionNameForModel(string $ability, string $model)
 *
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
