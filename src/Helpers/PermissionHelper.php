<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\InspireCmsConfig;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionHelper
{
    /**
     * Sets up the Super Admin role with all necessary permissions.
     *
     * This method is responsible for creating and configuring the Super Admin role,
     * ensuring that it has all the required permissions to manage the system.
     *
     * @return Role&Model
     */
    public static function setupSuperAdminRole()
    {
        $superAdminRoleName = PermissionManifest::getSuperAdminRoleName();

        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roleClass = InspireCmsConfig::getRoleModelClass();

        $permissions = static::setupPermissions();

        // create roles and assign created permissions
        $adminRole = $roleClass::findOrCreate($superAdminRoleName, static::getDefaultGuardName());

        // assign all permissions for "admin" role.
        $adminRole->syncPermissions($permissions);

        return $adminRole;
    }

    /**
     * Set up the permissions for the application.
     *
     * This method initializes and configures the necessary permissions
     * required by the application.
     *
     * @return Collection<Permission&Model>
     */
    public static function setupPermissions()
    {
        $guardName = static::getDefaultGuardName();

        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionClass = InspireCmsConfig::getPermissionModelClass();

        return PermissionManifest::permissions()->map(
            fn (string $permissionName) => $permissionClass::findOrCreate($permissionName, $guardName)
        );
    }

    public static function ensureTieredPermissions(array $permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->values()
            ->filter(fn ($permission) => static::isWildcardPattern($permission))
            ->values();

        $allPermissions = static::getCachedPermissions();

        static::cleanUnusedWildcardPermissions($permissions->all());
        
        $result = $allPermissions->whereIn('name', $permissions->all())->pluck('name')->all();

        $missing = array_diff($permissions->toArray(), $result);

        $guardName = InspireCmsConfig::getGuardName();
        $permissionClass = InspireCmsConfig::getPermissionModelClass();

        foreach ($missing as $permissionName) {
            $permissionClass::findOrCreate($permissionName, $guardName);
            $result[] = $permissionName;
        }

        return $result;
    }

    public static function cleanUnusedWildcardPermissions(array $excepts = [])
    {
        $existingWildcardPermissions = static::getCachedPermissions()
            ->keyBy(fn ($permission) => $permission->name)
            ->where(fn ($permission, $name) => static::isWildcardPattern($name))
            ->except($excepts)
            ->keyBy(fn ($permission) => $permission->getKey());
            
        $guardName = InspireCmsConfig::getGuardName();
        $permissionClass = InspireCmsConfig::getPermissionModelClass();
        /**
         * @var Builder
         */
        $query = app($permissionClass, ['attributes' => ['guard_name' => $guardName]])
            ->newQuery()
            ->where('guard_name', $guardName)
            ->whereKey($existingWildcardPermissions->keys()->all())
            ->where(fn ($query) => $query
                ->orDoesntHave('roles')
                ->orDoesntHave('users')
            );

        $query->cursor()->each->delete();
    }

    private static function isWildcardPattern(string $name)
    {
        return count(explode('.', $name)) === 3;
    }

    /**
     * @return Collection<Model>
     */
    public static function getCachedPermissions()
    {
        return app(PermissionRegistrar::class)->getPermissions(['guard_name' => static::getDefaultGuardName()]);
    }

    private static function getDefaultGuardName()
    {
        return InspireCmsConfig::getGuardName();
    }
}
