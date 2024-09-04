<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifestInterface;

/**
 * @method static \Illuminate\Support\Collection<string> permissions()
 * @method static \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\Manifest\UserRole> roles()
 * @method static void addRole(\SolutionForest\InspireCms\DataTypes\Manifest\UserRole $role)
 * @method static ?\SolutionForest\InspireCms\DataTypes\Manifest\UserRole getRole(string $name) Retrieves an role option by its name.
 * @method static array getClusterSectionPermissions()
 * @method static array getClusterSectionResourcePermissions()
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
