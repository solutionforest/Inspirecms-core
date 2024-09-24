<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

trait CanAuthorizeResource
{
    public static function skipAccessRightPermissionChecking(): bool
    {
        return config('inspirecms.skip_access_right_permission_on_resource', false);
    }

    public static function canAccess(): bool
    {
        if (in_array(ClusterSectionResource::class, class_implements(static::class))) {
            $cluster = static::getClusterSection();
            $permissionName = ! blank($cluster) && in_array(ClusterSection::class, class_implements($cluster)) ? $cluster::getAccessRightPermissionName() : null;

            if (! blank($permissionName)) {

                $user = Filament::auth()->user();

                return $user->can($permissionName);

            }
        }

        return static::canAccess();
    }

    public static function can(string $action, ?Model $record = null): bool
    {
        if (! static::skipAccessRightPermissionChecking()) {

            $model = $record ? get_class($record) : static::getModel();

            $result = PermissionManifest::authorizeModel($action, $model);

            if ($result !== null) {
                return $result;
            }
        }

        return parent::can($action, $record);
    }
}
