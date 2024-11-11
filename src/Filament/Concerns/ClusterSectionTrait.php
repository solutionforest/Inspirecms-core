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

    public static function getNavigationItems(): array
    {
        $items = parent::getNavigationItems();

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

                $newItems = collect($childComponents)
                    ->flatMap(function ($fqcn) {
                        if (is_subclass_of($fqcn, \Filament\Resources\Resource::class)) {
                            return $fqcn::getNavigationItems();
                        } elseif (is_subclass_of($fqcn, \Filament\Pages\Page::class)) {
                            return $fqcn::getNavigationItems();
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

                return $newItems;

            }
        }

        return $items;
    }
}
