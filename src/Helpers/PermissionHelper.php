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

        $guardName = AuthHelper::guardName();
        $permissionClass = InspireCmsConfig::getPermissionModelClass();

        foreach ($missing as $permissionName) {
            $permissionClass::findOrCreate($permissionName, $guardName);
            $result[] = $permissionName;
        }

        return $result;
    }

    public static function cleanUnusedWildcardPermissions(array $excepts = [])
    {
        $existingWildcardPermissions = collect(static::getWildcardPermissions())
            ->where(fn (Model $permission) => ! in_array($permission->name, $excepts));

        $guardName = AuthHelper::guardName();
        $permissionClass = InspireCmsConfig::getPermissionModelClass();
        /**
         * @var Builder
         */
        $query = app($permissionClass, ['attributes' => ['guard_name' => $guardName]])
            ->newQuery()
            ->where('guard_name', $guardName)
            ->whereKey($existingWildcardPermissions->keys()->all())
            ->where(
                fn ($query) => $query
                    ->orDoesntHave('roles')
                    ->orDoesntHave('users')
            );

        $query->cursor()->each->delete();
    }

    /**
     * @return Collection<string|int, Model>
     */
    public static function getWildcardPermissions(?string $model = null)
    {
        return static::getCachedPermissions()
            ->where(fn (Model $permission) => static::isWildcardPattern($permission->name))
            ->keyBy(fn (Model $permission) => $permission->getKey())
            ->when(
                $model,
                function (Collection $collection, $value) {

                    $prefix = str($value)->classBasename()->trim()->lower()->toString() . '.';

                    return $collection->where(fn ($permission) => str($permission->name)->startsWith($prefix));
                }
            );
    }

    /**
     * Explodes a wildcard permission string into its components.
     *
     * @param  string  $permissionName  The permission string in the format 'model.action.id'.
     * @return array<string, string> An associative array with keys 'model', 'action', and optionally 'id'.
     */
    public static function explodeWildcardPermission(string $permissionName)
    {
        $list = explode('.', $permissionName);

        $result = [];
        if (isset($list[0])) {
            $result['model'] = $list[0];
        }
        if (isset($list[1])) {
            $result['action'] = $list[1];
        }
        if (isset($list[2])) {
            $result['id'] = $list[2];
        }

        return $result;
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
        return AuthHelper::guardName();
    }

    private static function isWildcardPattern(string $name)
    {
        return count(static::explodeWildcardPermission($name)) === 3;
    }
}
