<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;

interface PermissionManifestInterface
{
    /**
     * @return \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\Manifest\UserPermission>
     */
    public function permissions(): Collection;

    /**
     * @return \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\Manifest\UserRole>
     */
    public function roles(): Collection;
}
