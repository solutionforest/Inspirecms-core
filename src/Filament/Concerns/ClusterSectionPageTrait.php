<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Facades\Filament;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;

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

    public static function canAccess(): bool
    {
        if (in_array(ClusterSectionPage::class, class_implements(static::class))) {
            $cluster = static::getClusterSection();

            $permissionName = ! blank($cluster) && in_array(ClusterSection::class, class_implements($cluster)) ? $cluster::getAccessRightPermissionName() : null;

            $user = Filament::auth()->user();

            if (! blank($permissionName) && $user) {

                return $user->can($permissionName);

            }
        }

        return parent::canAccess();
    }
}
