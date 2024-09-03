<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;

interface PermissionManifestInterface
{
    /**
     * @return \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\UserPermission>
     */
    public function permissions(): Collection;

    /**
     * @return \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\UserRole>
     */
    public function roles(): Collection;
}
