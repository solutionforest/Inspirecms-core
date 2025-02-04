<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
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
        $guardName = InspireCmsConfig::getGuardName();
        $superAdminRoleName = PermissionManifest::getSuperAdminRoleName();

        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roleClass = InspireCmsConfig::getRoleModelClass();

        $permissions = static::setupPermissions();

        // create roles and assign created permissions
        $adminRole = $roleClass::findOrCreate($superAdminRoleName, $guardName);

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
        $guardName = InspireCmsConfig::getGuardName();

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
            ->filter(fn ($permission) => count(explode('.', $permission)) === 3)
            ->values();
        
        $guardName = InspireCmsConfig::getGuardName();
        $permissionClass = InspireCmsConfig::getPermissionModelClass();
        
        return collect($permissions)->map(
            fn (string $permissionName) => $permissionClass::findOrCreate($permissionName, $guardName)
        )->pluck('name')->all();
    }
}
