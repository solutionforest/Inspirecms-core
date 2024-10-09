<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\Facades\InspireCms;

class PermissionManifest implements PermissionManifestInterface
{
    /** @var Collection<string> */
    protected Collection $permissions;

    protected string $superAdminRoleName = 'Admininistrator';

    public function __construct()
    {
        $this->permissions = collect($this->getDefaultPermissions());
    }

    public function getSuperAdminRoleName(): string
    {
        return $this->superAdminRoleName;
    }

    public function setSuperAdminRoleName(string $name): void
    {
        $this->superAdminRoleName = $name;
    }

    public function permissions(): Collection
    {
        return $this->permissions;
    }

    public function getClusterSectionPermissions(): array
    {
        return collect(InspireCms::getSections())
            ->map(fn (ClusterSection $section) => $section->getFqcn())
            ->where(fn ($fqcn) => is_subclass_of($fqcn, \Filament\Clusters\Cluster::class))
            ->where(fn ($fqcn) => in_array(\SolutionForest\InspireCms\Filament\Contracts\ClusterSection::class, class_implements($fqcn)))
            ->mapWithKeys(fn ($fqcn) => [$fqcn::getAccessRightPermissionName() => $fqcn::getNavigationLabel()])
            ->sortKeys()
            ->toArray();
    }

    public function getClusterSectionResourceModelPermissions(): array
    {
        return collect(config('inspirecms.filament.resources'))
            ->where(fn ($fqcn) => is_subclass_of($fqcn, \Filament\Resources\Resource::class))
            ->where(fn ($fqcn) => in_array(\SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource::class, class_implements($fqcn)))
            ->map(function ($fqcn) {

                $model = $fqcn::getModel();
                $modelShortName = class_basename($model);

                $permissionNames = collect($fqcn::getPermissionPrefixes())
                    ->mapWithKeys(function (string $prefix) use ($model) {

                        $permissionName = $this->getPermissionNameForModel($prefix, $model);

                        $permissionLabel = str($prefix)
                            ->studly()
                            ->toString();

                        return [$permissionName => $permissionLabel];
                    })
                    ->all();

                return [
                    'modelName' => $modelShortName,
                    'permissions' => $permissionNames,
                ];
            })
            ->reduce(function ($carry, array $value, $key) {
                $carry = collect($carry);

                $permissions = $value['permissions'];
                $modelName = $value['modelName'];

                if ($carry->has($modelName)) {
                    $permissions = collect($carry->get($modelName))->merge($permissions)->unique()->all();
                }

                return $carry->put($modelName, $permissions)
                    ->sortKeys()
                    ->toArray();
            });
    }

    /**
     * Get the permission name for a given model and ability.
     *
     * @param  string  $ability  The ability for which the permission name is required.
     * @param  string  $model  The fqcn of the model.
     * @return string The permission name for the specified model and ability.
     */
    public function getPermissionNameForModel(string $ability, string $model): string
    {
        $modelShortName = class_basename($model);

        return str($modelShortName)
            ->lower()
            ->snake('_')
            ->prepend('_')
            ->prepend($ability)
            ->toString();
    }

    public function authorizeModel(string $ability, string $model, bool $checkExist = true): ?bool
    {
        $modelShortName = class_basename($model);

        if ($checkExist) {

            if (! class_exists($model)) {
                return null;
            }

            $permissionNames = data_get(PermissionManifest::getClusterSectionResourceModelPermissions(), $modelShortName);

            $permissionNameToCheck = collect($permissionNames)->filter(fn ($label) => lcfirst($label) === $ability)->keys()->first();

            if (! $permissionNameToCheck) {
                return null;
            }
        }

        $permissionName = $this->getPermissionNameForModel(Str::snake($ability), $model);

        return auth()->user()->can($permissionName);
    }

    //region Helper methods
    protected function getDefaultPermissions(): array
    {
        return $this->getEntitiesPermissions();
    }

    protected function getEntitiesPermissions(): array
    {
        return collect($this->getClusterSectionPermissions())->keys()
            ->merge(collect($this->getClusterSectionResourceModelPermissions())->collapse()->keys())
            ->map(fn ($permission) => str($permission)->lower()->toString())
            ->values()
            ->unique()
            ->toArray();
    }
    //endregion Helper methods
}
