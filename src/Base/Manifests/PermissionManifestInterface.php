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

    public function getWidgetPermissions(): array;

    public function getActionPermissions(): array;

    public function getTieredPermissions(): array;

    public function isTieredPermissionGranted(string $model): bool;

    /**
     * Retrieve the model associated with a tiered permission based on the provided label.
     *
     * @param  string  $label  The label of the tiered permission.
     * @return string|null The model associated with the tiered permission, or null if not found.
     */
    public function getModelForTieredPermission(string $label): ?string;

    /**
     * Get the permission name for a given model and ability.
     *
     * @param  string  $ability  The ability for which the permission name is required.
     * @param  string  $model  The fqcn of the model.
     * @return string The permission name for the specified model and ability.
     */
    public function getPermissionNameForModel(string $ability, string $model): string;

    /**
     * Get the tiered permission name for a specific model.
     *
     * @param  string  $ability  The ability or action for which the permission is being checked.
     * @param  string  $model  The name of the model for which the permission is being checked.
     * @param  string|int  $id  The identifier of the specific model instance.
     * @return string The tiered permission name for the specified model and ability.
     */
    public function getTieredPermissionNameForModel(string $ability, string $model, $id): string;

    /**
     * Authorizes a specific ability for a given model.
     *
     * @param  string  $ability  The ability to be checked (e.g., 'view', 'edit').
     * @param  string  $model  The model for which the ability is being checked.
     * @param  bool  $checkExist  Optional. Whether to check if the model exists. Default is true.
     * @param  string|int  $id  Optional. The identifier of the model instance. Default is null.
     * @return bool|null Returns true if the ability is authorized, false if not authorized, or null if the permission does not exist and $checkExist is true.
     */
    public function authorizeModel(string $ability, string $model, bool $checkExist = true, $id = null): ?bool;

    /**
     * Authorizes the specified action.
     *
     * @param  string  $actionFqcn  The fully qualified class name of the action to authorize.
     * @return bool|null Returns true if the action is authorized, false if it is not, or null if the authorization status is indeterminate.
     */
    public function authorizeAction(string $actionFqcn): ?bool;

    /**
     * Authorizes the given widget based on its fully qualified class name (FQCN).
     *
     * @param  string  $widgetFqcn  The fully qualified class name of the widget to authorize.
     * @return bool|null Returns true if the widget is authorized, false if not authorized,
     *                   or null if the authorization status cannot be determined.
     */
    public function authorizeWidget(string $widgetFqcn): ?bool;
}
