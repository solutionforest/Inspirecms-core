<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Facades\Filament;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;

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

            if (blank($permissionName) || !$user) {
                continue;
            }

            if (! $user->can($permissionName)) {
                return false;
            }
        }

        return parent::canAccess();
    }
}
