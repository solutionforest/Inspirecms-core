<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Concerns;

use Filament\Resources\Pages\ListRecords;

trait ConfigureContentResourcePageSubNavigation
{
    use ConfigureContentsSubNavigation;

    public function getSubNavigation(): array
    {
        $subNavigation = parent::getSubNavigation();

        if (!$this instanceof ListRecords) {
            $subNavigation = $this->getSubNavigationForNonListPage();
        }

        $cluster = static::getCluster();
        if ($cluster) {

            $clusterNav = clone $cluster::getNavigationItems()[0] ?? null;

            if ($clusterNav) {
                $clusterNav
                    ->sort(-999)
                    ->icon('heroicon-s-chevron-left')
                    ->activeIcon(null)
                    ->isActiveWhen(fn () => false);
                $subNavigation = array_merge([$clusterNav], $subNavigation);
            }
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
