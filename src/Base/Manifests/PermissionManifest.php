<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\DataTypes\Manifest\UserRole;
use SolutionForest\InspireCms\Facades\InspireCms;

class PermissionManifest implements PermissionManifestInterface
{
    /** @var Collection<string> */
    protected Collection $permissions;

    /** @var Collection<UserRole> */
    protected Collection $roles;

    public function __construct()
    {
        $this->permissions = collect($this->getDefaultPermissions());
        $this->roles = collect($this->getDefaultRoles());
    }

    public function permissions(): Collection
    {
        return $this->permissions;
    }

    public function roles(): Collection
    {
        return $this->roles;
    }
    
    public function addRole(UserRole $role): void
    {
        $exist = $this->getRole($role->getName());
        if (! $exist) {
            $this->roles->push($role);
        }
    }

    public function getRole(string $name): ?UserRole
    {
        return $this->roles->first(fn (UserRole $role) => $role->getName() === $name);
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

    public function getClusterSectionResourcePermissions(): array
    {
        return collect(config('inspirecms.resources'))
            ->where(fn ($fqcn) => is_subclass_of($fqcn, \Filament\Resources\Resource::class))
            ->where(fn ($fqcn) => in_array(\SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource::class, class_implements($fqcn)))
            ->mapWithKeys(function ($fqcn) {
                $permissionNames = collect($fqcn::getPermissionPrefixes())
                    ->mapWithKeys(function (string $prefix) use ($fqcn) {

                        $permissionName = str($fqcn::getModelLabel())
                            ->lower()
                            ->snake('_')
                            ->prepend('_')
                            ->prepend($prefix)
                            ->toString();

                        $permissionLabel = str($prefix)
                            ->studly()
                            ->toString();

                        return [$permissionName => $permissionLabel];
                    })
                    ->all();

                return [
                    $fqcn => $permissionNames
                ];
            })
            ->sortKeys()
            ->toArray();
    }

    //region Helper methods
    protected function getDefaultPermissions(): array
    {
        return $this->getEntitiesPermissions();
    }

    protected function getDefaultRoles(): array
    {
        return [
            new UserRole('admin', __('inspirecms::permissions.roles.admin.label')),
            new UserRole('editor', __('inspirecms::permissions.roles.editor.label')),
            new UserRole('writer', __('inspirecms::permissions.roles.writer.label')),
        ];
    }

    protected function getEntitiesPermissions(): array
    {
        return collect($this->getClusterSectionPermissions())->keys()
            ->merge(collect($this->getClusterSectionResourcePermissions())->collapse()->keys())
            ->map(fn ($permission) => str($permission)->lower()->toString())
            ->values()
            ->unique()
            ->toArray();
    }
    //endregion Helper methods
}
