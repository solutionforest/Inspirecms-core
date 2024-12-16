<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifestInterface;

/**
 * @method static string getSuperAdminRoleName()
 * @method static void setSuperAdminRoleName(string $name)
 * @method static \Illuminate\Support\Collection<string> permissions()
 * @method static array getClusterSectionPermissions()
 * @method static array getResourcePermissions()
 * @method static array getActionPermissions()
 * @method static array getPagePermissions()
 * @method static string getPermissionNameForModel(string $ability, string $model)
 * @method static ?bool authorizeModel(string $ability, string $model, bool $checkExist = true)
 * @method static ?bool authorizeAction(string $actionFqcn)
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
