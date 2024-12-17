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

    public function getResourcePermissions(): array;

    /**
     * Get the permission name for a given model and ability.
     *
     * @param  string  $ability  The ability for which the permission name is required.
     * @param  string  $model  The fqcn of the model.
     * @return string The permission name for the specified model and ability.
     */
    public function getPermissionNameForModel(string $ability, string $model): string;

    /**
     * Authorizes a specific ability for a given model.
     *
     * @param  string  $ability  The ability to be checked (e.g., 'view', 'edit').
     * @param  string  $model  The model for which the ability is being checked.
     * @param  bool  $checkExist  Optional. Whether to check if the model exists. Default is true.
     * @return bool|null Returns true if the ability is authorized, false if not authorized, or null if the permission does not exist and $checkExist is true.
     */
    public function authorizeModel(string $ability, string $model, bool $checkExist = true): ?bool;

    /**
     * Authorizes the specified action.
     *
     * @param  string  $actionFqcn  The fully qualified class name of the action to authorize.
     * @return bool|null Returns true if the action is authorized, false if it is not, or null if the authorization status is indeterminate.
     */
    public function authorizeAction(string $actionFqcn): ?bool;
}
