<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;
use SolutionForest\InspireCms\InspireCmsConfig;

trait ClusterSectionPageTrait
{
    public static function getClusterSection(): string
    {
        $cluster = static::getCluster();

        if (blank($cluster)) {
            throw new \Exception('The section cluster is not defined. Please ensure that the cluster configuration is set correctly.');
        }

        return $cluster;
    }

    public function getBreadcrumbs(): array
    {
        return [
            ...parent::getBreadcrumbs(),
            static::getNavigationLabel(),
        ];
    }

    public static function canAccess(): bool
    {
        $inplements = class_implements(static::class);

        $permissionsToCheck = [];

        if (in_array(ClusterSectionPage::class, $inplements)) {
            $cluster = static::getClusterSection();

            $permissionsToCheck[] = ! blank($cluster) && in_array(ClusterSection::class, class_implements($cluster)) ? $cluster::getAccessRightPermissionName() : null;

        }

        if (in_array(GuardPage::class, $inplements)) {

            $permissionsToCheck[] = static::getPermissionName();
        }

        foreach ($permissionsToCheck as $permissionName) {

            $user = Filament::auth()->user();

            if (blank($permissionName) || ! $user) {
                continue;
            }

            if (! $user->can($permissionName)) {
                return false;
            }
        }

        return parent::canAccess();
    }

    /**
     * @return array<NavigationItem | NavigationGroup>
     */
    public function getSubNavigation(): array
    {
        if (InspireCmsConfig::get('admin.enable_cluster_navigation') && filled($cluster = static::getCluster())) {
            $items = $this->generateNavigationItems($cluster::getClusteredComponents());

            return array_map(fn ($item) => static::configurePageKeyOnNavigationItem($item), $items);
        }

        return [];
    }

    public static function getNavigationItems(): array
    {
        $items = parent::getNavigationItems();

        return array_map(fn ($item) => static::configurePageKeyOnNavigationItem($item), $items);
    }

    /**
     * @param  \Filament\Navigation\NavigationItem  $navigationItem
     * @return \Filament\Navigation\NavigationItem
     */
    public static function configurePageKeyOnNavigationItem($navigationItem)
    {
        $section = static::getClusterSection();

        return $section::configurePageKeyOnNavigationItem(static::class, $navigationItem);
    }
}
