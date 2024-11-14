<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Navigation\NavigationItem;

trait ClusterSectionTrait
{
    public static function getAccessRightPermissionName(): string
    {
        return 'access_section_cluster_' . strtolower(trim(class_basename(static::class)));
    }

    public static function canAccess(): bool
    {
        return filament()->auth()->user()->can(static::getAccessRightPermissionName());
    }

    protected static function getSectionKey(): string
    {
        return strtolower(trim(class_basename(static::class)));
    }

    /**
     * @param  \Filament\Navigation\NavigationItem|\SolutionForest\InspireCms\Filament\Navigation\NavigationItem  $navigationItem
     * @return \Filament\Navigation\NavigationItem|\SolutionForest\InspireCms\Filament\Navigation\NavigationItem
     */
    public static function configureSectionKeyOnNavigationItem($navigationItem)
    {
        return $navigationItem->section(static::getSectionKey());
    }

    /**
     * @param  \Filament\Navigation\NavigationItem|\SolutionForest\InspireCms\Filament\Navigation\NavigationItem  $navigationItem
     * @return \Filament\Navigation\NavigationItem|\SolutionForest\InspireCms\Filament\Navigation\NavigationItem
     */
    public static function configureResourceKeyOnNavigationItem($resourceFqcn, $navigationItem)
    {
        return $navigationItem
            ->section(static::getSectionKey())
            ->itemKey((string) str(class_basename($resourceFqcn))->trim()->beforeLast('Resource')->snake()->lower());
    }

    /**
     * @param  \Filament\Navigation\NavigationItem|\SolutionForest\InspireCms\Filament\Navigation\NavigationItem  $navigationItem
     * @return \Filament\Navigation\NavigationItem|\SolutionForest\InspireCms\Filament\Navigation\NavigationItem
     */
    public static function configurePageKeyOnNavigationItem($pageFqcn, $navigationItem)
    {
        return $navigationItem
            ->section(static::getSectionKey())
            ->itemKey((string) str(class_basename($pageFqcn))->trim()->snake()->lower());
    }

    public static function getNavigationItems(): array
    {
        $items = collect(parent::getNavigationItems())
            ->map(function ($item) {
                if ($item instanceof \Filament\Navigation\NavigationItem | $item instanceof \SolutionForest\InspireCms\Filament\Navigation\NavigationItem) {
                    return static::configureSectionKeyOnNavigationItem($item);
                }

                return $item;
            })
            ->all();

        if (config('inspirecms.filament.enable_cluster_navigation')) {
            return $items;
        }

        if (count($items) == 1) {
            $item = $items[0];
            if ($item instanceof NavigationItem && ! $item->getGroup()) {
                $childComponents = static::getClusteredComponents();

                if (empty($childComponents)) {
                    return [$item->group($item->getLabel())];
                }

                return collect($childComponents)
                    ->flatMap(function ($fqcn) {
                        if (is_subclass_of($fqcn, \Filament\Resources\Resource::class)) {
                            return array_map(fn ($item) => static::configureResourceKeyOnNavigationItem($fqcn, $item), $fqcn::getNavigationItems());
                        } elseif (is_subclass_of($fqcn, \Filament\Pages\Page::class)) {
                            return array_map(fn ($item) => static::configurePageKeyOnNavigationItem($fqcn, $item), $fqcn::getNavigationItems());
                        } else {
                            return null;
                        }
                    })
                    ->filter()
                    ->map(
                        fn (NavigationItem $navItem) => $navItem
                            ->group($item->getLabel())
                    )
                    ->toArray();

            }
        }

        return $items;
    }
}
