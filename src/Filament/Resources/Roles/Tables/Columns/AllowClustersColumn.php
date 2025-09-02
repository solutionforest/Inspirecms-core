<?php

namespace SolutionForest\InspireCms\Filament\Resources\Roles\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use Spatie\Permission\Models\Role;

class AllowClustersColumn
{
    public static function make()
    {
        return TextColumn::make('allow_clusters')
            ->label(__('inspirecms::resources/role.cluster_section_access.label'))
            ->getStateUsing(function (Role $record) {
                $clusterSectionPermissions = PermissionManifest::getClusterSectionPermissions();
                $allowSections = $record->getPermissionNames()
                    ->intersect(array_keys($clusterSectionPermissions))
                    ->map(fn ($permissionName) => $clusterSectionPermissions[$permissionName]);

                return $allowSections->implode(', ');
            })
            ->limit(50);
    }
}
