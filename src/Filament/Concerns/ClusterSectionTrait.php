<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

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
}
