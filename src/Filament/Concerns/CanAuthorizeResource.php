<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Facades\Filament;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

trait CanAuthorizeResource
{
    public static function canAccess(): bool
    {
        if (in_array(ClusterSectionResource::class, class_implements(static::class))) {
            $cluster = static::getClusterSection();
            $permissionName = ! blank($cluster) && in_array(ClusterSection::class, class_implements($cluster)) ? $cluster::getAccessRightPermissionName() : null;

            if (!blank($permissionName)) {

                $user = Filament::auth()->user();

                return $user->can($permissionName);

            }
        }
        
        return static::canAccess();
    }
}
