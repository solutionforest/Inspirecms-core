<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\UserRole;

interface PermissionManifestInterface
{
    /**
     * @return \Illuminate\Support\Collection<string>
     */
    public function permissions(): Collection;

    /**
     * @return \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\Manifest\UserRole>
     */
    public function roles(): Collection;

    public function addRole(UserRole $role): void;

    /**
     * Retrieves an role option by its name.
     * @param string $name
     * @return ?\SolutionForest\InspireCms\DataTypes\Manifest\UserRole
     */
    public function getRole(string $name): ?UserRole;

    public function getClusterSectionPermissions(): array;

    public function getClusterSectionResourcePermissions(): array;
}
