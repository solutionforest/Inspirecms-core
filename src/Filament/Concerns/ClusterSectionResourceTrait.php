<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;
use SolutionForest\InspireCms\InspireCmsConfig;

trait ClusterSectionResourceTrait
{
    use CanAuthorizeResource;

    /**
     * Get the cluster section.
     * 
     * @throws \Exception
     * @return class-string<ClusterSection>
     */
    public static function getClusterSection(): string
    {
        $cluster = static::getCluster();

        if (blank($cluster)) {
            throw new \Exception('The section cluster is not defined. Please ensure that the cluster configuration is set correctly.');
        }

        if (! in_array(ClusterSection::class, class_implements($cluster))) {
            throw new \Exception("The cluster must implement the ClusterSection interface.");
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
        if (InspireCmsConfig::get('filament.enable_cluster_navigation') && filled($cluster = static::getCluster())) {
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

        if (method_exists($section, 'configureResourceKeyOnNavigationItem')) {
            return $section::configureResourceKeyOnNavigationItem(static::class, $navigationItem);
        }

        return $navigationItem;
    }
}
