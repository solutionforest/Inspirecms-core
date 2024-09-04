<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

trait ClusterSectionTrait
{
    public static function getAccessRightPermissionName(): string
    {
        return 'access_section_cluster_' . strtolower(trim(class_basename(static::class)));
    }
}
