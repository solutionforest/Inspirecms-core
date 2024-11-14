<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

trait ClusterSectionResourceTrait
{
    use CanAuthorizeResource;

    public static function getClusterSection(): string
    {
        $cluster = static::getCluster();

        if (blank($cluster)) {
            throw new \Exception('The section cluster is not defined. Please ensure that the cluster configuration is set correctly.');
        }

        return $cluster;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function getRecordSubNavigation(\Filament\Resources\Pages\Page $page): array
    {
        if (config('inspirecms.filament.enable_cluster_navigation') && filled($cluster = static::getCluster())) {
            $items = $page->generateNavigationItems($cluster::getClusteredComponents());

            return array_map(fn ($item) => static::configureResourceKeyOnNavigationItem($item), $items);
        }

        return [];
    }

    public static function getNavigationItems(): array
    {
        $items = parent::getNavigationItems();

        return array_map(fn ($item) => static::configureResourceKeyOnNavigationItem($item), $items);
    }

    /**
     * @param  \Filament\Navigation\NavigationItem|\SolutionForest\InspireCms\Filament\Navigation\NavigationItem  $navigationItem
     * @return \Filament\Navigation\NavigationItem|\SolutionForest\InspireCms\Filament\Navigation\NavigationItem
     */
    public static function configureResourceKeyOnNavigationItem($navigationItem)
    {
        $section = static::getClusterSection();

        return $section::configureResourceKeyOnNavigationItem(static::class, $navigationItem);
    }
}
