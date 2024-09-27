<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\ListRecords;

trait ConfigureContentResourcePageSubNavigation
{
    use ConfigureContentsSubNavigation;

    public function getSubNavigation(): array
    {
        $subNavigation = parent::getSubNavigation();

        if (! $this instanceof ListRecords) {
            $subNavigation = $this->getSubNavigationForNonListPage();
        }

        $cluster = static::getCluster();
        if ($cluster) {

            $subNavigation = array_merge([
                NavigationItem::make($cluster::getNavigationLabel())
                    ->url($cluster::getUrl())
                    ->icon('heroicon-s-chevron-left')
                    ->activeIcon(null)
                    ->isActiveWhen(fn () => false)
                    ->sort(-999),
            ], $subNavigation);
        }

        return $subNavigation;
    }

    public function getSubNavigationForNonListPage(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
