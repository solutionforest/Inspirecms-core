<?php

namespace SolutionForest\InspireCms\Base\Filament\Pages\Concerns;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\SubNavigationPosition;

trait HasExtraSubNavigation
{
    /**
     * @var array<NavigationGroup>
     */
    protected array $cachedExtraSubNavigation;

    protected static SubNavigationPosition $extraSubNavigationPosition = SubNavigationPosition::Top;

    protected function getExtraSubNavigationComponents(): array
    {
        return [];
    }

    /**
     * @return array<NavigationItem | NavigationGroup>
     */
    public function getExtraSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($this->getExtraSubNavigationComponents());
        }

        return [];
    }

    public function getExtraSubNavigationPosition(): SubNavigationPosition
    {
        return static::$extraSubNavigationPosition;
    }

    /**
     * @return array<NavigationGroup>
     */
    public function getCachedExtraSubNavigation(): array
    {
        if (isset($this->cachedExtraSubNavigation)) {
            return $this->cachedExtraSubNavigation;
        }

        $navigationItems = [];

        $navigationGroups = [];

        foreach ($this->getExtraSubNavigation() as $item) {
            if ($item instanceof NavigationGroup) {
                $navigationGroups[$item->getLabel()] = $item;

                continue;
            }

            $navigationItems[] = $item;
        }

        $navigationItems = collect($navigationItems)
            ->sortBy(fn (NavigationItem $item): int => $item->getSort())
            ->filter(function (NavigationItem $item) use (&$navigationGroups): bool {
                if (! $item->isVisible()) {
                    return false;
                }

                $itemGroup = $item->getGroup();

                if (array_key_exists($itemGroup, $navigationGroups)) {
                    $navigationGroups[$itemGroup]->items([
                        ...$navigationGroups[$itemGroup]->getItems(),
                        $item,
                    ]);

                    return false;
                }

                if (filled($itemGroup)) {
                    $navigationGroups[$itemGroup] = NavigationGroup::make()
                        ->label($itemGroup)
                        ->items([$item]);

                    return false;
                }

                return true;
            })
            ->all();

        foreach ($navigationGroups as $navigationGroup) {
            $navigationGroup->items(
                collect($navigationGroup->getItems())
                    ->filter(fn (NavigationItem $item): bool => $item->isVisible())
                    ->sortBy(fn (NavigationItem $item): int => $item->getSort())
                    ->all(),
            );
        }

        return $this->cachedExtraSubNavigation = [
            ...($navigationItems ? [NavigationGroup::make()->items($navigationItems)] : []),
            ...$navigationGroups,
        ];
    }
}
