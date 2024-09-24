<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

trait ClusterSectionResourceTrait
{
    use CanAuthorizeResource;

    public static function getClusterSection(): string
    {
        $cluster = static::getCluster();

        if (blank($cluster)) {
            throw new \Exception('The section cluster is not defined. Please ensure that the cluster configuration is set correctly.');
        }

        return $cluster;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
