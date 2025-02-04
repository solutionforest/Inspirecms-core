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
 * @method static array getWidgetPermissions()
 * @method static array getActionPermissions()
 * @method static array getPagePermissions()
 * @method static array getTieredPermissions()
 * @method static string getPermissionNameForModel(string $ability, string $model)
 * @method static string getTieredPermissionNameForModel(string $ability, string $model, $id)
 * @method static ?string getModelForTieredPermission(string $label)
 * @method static ?bool authorizeModel(string $ability, string $model, bool $checkExist = true, $id = null)
 * @method static ?bool authorizeAction(string $actionFqcn)
 * @method static ?bool authorizeWidget(string $widgetFqcn)
 * @method static bool isTieredPermissionGranted(string $model)
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
