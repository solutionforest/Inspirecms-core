<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Contracts\GuardAction;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Resources\ExportResource;
use SolutionForest\InspireCms\Filament\Resources\ImportResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class PermissionManifest implements PermissionManifestInterface
{
    /** @var Collection<string> */
    protected Collection $permissions;

    protected string $superAdminRoleName = 'Administrator';

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

    public function getResourcePermissions(): array
    {
        $resourcesInConfig = InspireCmsConfig::getFilamentResources();

        $panel = filament()->getPanel(InspireCmsConfig::getPanelId());
        $resourcesFromDiscover = collect($panel?->getResourceNamespaces() ?? [])
            ->combine($panel?->getResourceDirectories() ?? [])
            // get all resource from the discover directories
            ->flatMap(function ($dir, $namespace) {
                // get the fully qualified class names of the pages
                $files = glob($dir . '/*.php');

                return collect($files)
                    ->map(fn ($file) => str_replace(['/', '.php'], ['\\', ''], $file))
                    ->map(fn ($fqcn) => $namespace . '\\' . class_basename($fqcn));
            })
            ->all();

        $resourcePermissions = collect($resourcesInConfig)
            ->merge($resourcesFromDiscover)
            ->merge([
                ImportResource::class,
                ExportResource::class,
            ])
            ->unique() // ensure no duplicates
            ->where(
                fn (string $fqcn): bool => is_subclass_of($fqcn, \Filament\Resources\Resource::class) &&
                in_array(ClusterSectionResource::class, class_implements($fqcn))
            )
            ->map(fn (string $fqcn) => [
                'model' => $fqcn::getModel(),
                'customModelName' => $fqcn::getCustomModelPermissionPrefix(),
                'customModelDisplay' => $fqcn::getCustomModelPermissionDisplay(),
                'permissionPrefixes' => $fqcn::getPermissionPrefixes(),
            ])
            // add MediaAsset model permissions
            ->merge([
                [
                    'model' => \SolutionForest\InspireCms\Support\Facades\ModelRegistry::get(MediaAsset::class),
                    'permissionPrefixes' => ['view', 'create', 'update', 'delete', 'delete_any'],
                ],
            ])
            ->all();

        return collect($resourcePermissions)
            ->map(function (array $data) {

                $model = $data['model'];
                $customModelPermissionPrefix = $data['customModelName'] ?? null;
                $modelShortName = $data['customModelDisplay'] ?? class_basename($model);

                $permissionNames = collect($data['permissionPrefixes'])
                    ->mapWithKeys(callback: function (string $suffix) use ($model, $customModelPermissionPrefix) {

                        $permissionName = is_string($customModelPermissionPrefix) && filled($customModelPermissionPrefix)
                            ? $this->getPermissionNameForModelShort($suffix, $customModelPermissionPrefix)
                            : $this->getPermissionNameForModel($suffix, $model);

                        $permissionLabel = str($suffix)
                            ->kebab()
                            ->replace(['-', '_'], ' ')
                            ->ucfirst()
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

    public function getWidgetPermissions(): array
    {
        return collect(InspireCmsConfig::get('permissions.guard_widgets'))
            ->where(fn ($fqcn) => in_array(GuardWidget::class, class_implements($fqcn)))
            ->mapWithKeys(fn ($fqcn) => [$fqcn::getPermissionName() => $fqcn::getPermissionDisplayName()])
            ->sortKeys()
            ->toArray();
    }

    public function getActionPermissions(): array
    {
        return collect(InspireCmsConfig::get('permissions.guard_actions'))
            ->where(fn ($fqcn) => in_array(GuardAction::class, class_implements($fqcn)))
            ->mapWithKeys(fn ($fqcn) => [$fqcn::getPermissionName() => $fqcn::getPermissionDisplayName()])
            ->sortKeys()
            ->toArray();
    }

    public function getPagePermissions(): array
    {
        $pagesInConfig = InspireCmsConfig::getFilamentPages();

        $panel = filament()->getPanel(InspireCmsConfig::getPanelId());
        $pagesFromDiscover = collect($panel?->getPageNamespaces() ?? [])
            ->combine($panel?->getPageDirectories() ?? [])
            // get all pages from the discover directories
            ->flatMap(function ($dir, $namespace) {
                // get the fully qualified class names of the pages
                $files = glob($dir . '/*.php');

                return collect($files)
                    ->map(fn ($file) => str_replace(['/', '.php'], ['\\', ''], $file))
                    ->map(fn ($fqcn) => $namespace . '\\' . class_basename($fqcn));
            })
            ->all();

        return collect($pagesInConfig)
            ->merge($pagesFromDiscover)
            ->unique() // ensure no duplicates
            ->where(fn ($fqcn) => in_array(GuardPage::class, class_implements($fqcn)))
            ->mapWithKeys(fn ($fqcn) => [$fqcn::getPermissionName() => $fqcn::getPermissionDisplayName()])
            ->sortKeys()
            ->toArray();
    }

    public function getTieredPermissions(): array
    {
        return collect($this->getResourcePermissions())
            ->only(['Content'])
            // filter out "any" permissions
            ->map(
                fn ($permissions) => collect($permissions)
                    ->filter(fn ($label, $name) => $this->isAbilityGrantedWithTier(Str::afterLast($name, '.')))
                    ->all()
            )
            ->all();
    }

    public function getModelForTieredPermission(string $label): ?string
    {
        return match (Str::of($label)->trim()->lower()->toString()) {
            'content' => InspireCmsConfig::getContentModelClass(),
            default => null,
        };
    }

    public function isTieredPermissionGranted(string $model): bool
    {
        return in_array(Str::of($model)->trim()->lower()->toString(), [
            // Only support content for now
            'content',
        ]);
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

        return $this->getPermissionNameForModelShort($ability, $modelShortName);
    }

    protected function getPermissionNameForModelShort(string $ability, string $model): string
    {
        return str($model)
            ->lower()
            ->snake('_')
            ->finish('.')
            ->finish($ability)
            ->toString();
    }

    public function getTieredPermissionNameForModel(string $ability, string $model, $id): string
    {
        return implode('.', [$this->getPermissionNameForModel($ability, $model), $id]);
    }

    public function authorizeModel(string $ability, string $model, bool $checkExist = true, $id = null): ?bool
    {
        $modelShortName = class_basename($model);

        if ($checkExist) {

            if (! class_exists($model)) {
                return null;
            }

            $permissionNames = data_get($this->getResourcePermissions(), $modelShortName);

            $permissionNameToCheck = collect($permissionNames)->filter(fn ($label) => lcfirst($label) === $ability)->keys()->first();

            if (! $permissionNameToCheck) {
                return null;
            }
        }

        $user = auth()->user();
        if (! $user) {
            return null;
        }

        // Check tiered permission if needed
        if ($id != null
            && $this->isTieredPermissionGranted($modelShortName)
            && $this->isAbilityGrantedWithTier($ability)
            && ($tieredPermission = $this->getTieredPermissionNameForModel($ability, $model, $id))
            && $tieredPermission != null
            && $user->can($tieredPermission) == true
        ) {
            return true;
        }

        $permissionName = $this->getPermissionNameForModel(Str::snake($ability), $model);

        return $user->can($permissionName);
    }

    public function authorizeAction(string $actionFqcn): ?bool
    {
        if (! class_exists($actionFqcn) || ! in_array(GuardAction::class, class_implements($actionFqcn))) {
            return null;
        }

        $permissionName = $actionFqcn::getPermissionName();

        if (blank($permissionName)) {
            return null;
        }

        return auth()->user()?->can($permissionName);
    }

    public function authorizeWidget(string $widgetFqcn): ?bool
    {
        if (! class_exists($widgetFqcn) || ! in_array(GuardWidget::class, class_implements($widgetFqcn))) {
            return null;
        }

        $permissionName = $widgetFqcn::getPermissionName();

        if (blank($permissionName)) {
            return null;
        }

        return auth()->user()?->can($permissionName);
    }

    // region Helper methods
    protected function getDefaultPermissions(): array
    {
        return collect($this->getClusterSectionPermissions())->keys()
            ->merge(collect($this->getResourcePermissions())->collapse()->keys())
            ->merge(collect($this->getActionPermissions())->keys())
            ->merge(collect($this->getPagePermissions())->keys())
            ->merge(collect($this->getWidgetPermissions())->keys())
            ->map(fn ($permission) => str($permission)->lower()->toString())
            ->values()
            ->unique()
            ->toArray();
    }

    protected function isAbilityGrantedWithTier($ability): bool
    {
        return ! Str::endsWith($ability, '_any') && $ability != 'create';
    }
    // endregion Helper methods
}
