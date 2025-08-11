<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use SolutionForest\InspireCms\InspireCmsConfig;

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
     * @param  NavigationItem  $navigationItem
     * @return NavigationItem
     */
    public static function configureSectionKeyOnNavigationItem($navigationItem)
    {
        // todo: implement section key configuration
        return $navigationItem;
    }

    /**
     * @param  NavigationItem  $navigationItem
     * @return NavigationItem
     */
    public static function configureResourceKeyOnNavigationItem($resourceFqcn, $navigationItem)
    {
        // todo: implement resource key configuration
        return $navigationItem;
    }

    /**
     * @param  NavigationItem  $navigationItem
     * @return NavigationItem
     */
    public static function configurePageKeyOnNavigationItem($pageFqcn, $navigationItem)
    {
        // todo: implement page key configuration
        return $navigationItem;
    }

    public static function getNavigationItems(): array
    {
        $items = collect(parent::getNavigationItems())
            ->map(function ($item) {
                if ($item instanceof NavigationItem) {
                    return static::configureSectionKeyOnNavigationItem($item);
                }

                return $item;
            })
            ->all();

        if (InspireCmsConfig::get('admin.enable_cluster_navigation')) {
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
                        if (is_subclass_of($fqcn, Resource::class)) {
                            return array_map(fn ($item) => static::configureResourceKeyOnNavigationItem($fqcn, $item), $fqcn::getNavigationItems());
                        } elseif (is_subclass_of($fqcn, Page::class)) {
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
