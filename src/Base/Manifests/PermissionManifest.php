<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\UserPermission;
use SolutionForest\InspireCms\DataTypes\Manifest\UserRole;

class PermissionManifest implements PermissionManifestInterface
{
    /** @var Collection<UserPermission> */
    protected Collection $permissions;

    /** @var Collection<UserRole> */
    protected Collection $roles;

    public function __construct()
    {
        $this->permissions = collect(static::getDefaultPermissions());
        $this->roles = collect(static::getDefaultRoles());
    }

    public function permissions(): Collection
    {
        return $this->permissions;
    }

    public function roles(): Collection
    {
        return $this->roles;
    }

    protected static function getDefaultPermissions(): array
    {
        return [
            new UserPermission('cms_content.create'),
        ];
    }

    protected static function getDefaultRoles(): array
    {
        return [
            new UserRole('admin'),
            new UserRole('editor'),
            new UserRole('writer'),
        ];
    }
}
