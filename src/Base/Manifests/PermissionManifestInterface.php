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
}
