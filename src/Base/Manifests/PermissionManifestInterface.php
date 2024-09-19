<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;

interface PermissionManifestInterface
{
    public function getSuperAdminRoleName(): string;

    public function setSuperAdminRoleName(string $name): void;

    /**
     * @return \Illuminate\Support\Collection<string>
     */
    public function permissions(): Collection;

    public function getClusterSectionPermissions(): array;

    public function getClusterSectionResourceModelPermissions(): array;

    /**
     * Get the permission name for a given model and ability.
     *
     * @param  string  $ability  The ability for which the permission name is required.
     * @param  string  $model  The fqcn of the model.
     * @return string The permission name for the specified model and ability.
     */
    public function getPermissionNameForModel(string $ability, string $model): string;
}
