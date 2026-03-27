<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use UnitEnum;

trait CanAuthorizeResource
{
    public static function skipAccessRightPermissionChecking(): bool
    {
        return InspireCmsConfig::get('skip_access_right_permission_on_resource', false);
    }

    public static function canAccess(): bool
    {
        if (in_array(ClusterSectionResource::class, class_implements(static::class))) {
            $cluster = static::getClusterSection();
            $permissionName = ! blank($cluster) ? $cluster::getAccessRightPermissionName() : null;

            if (! blank($permissionName)) {

                $user = Filament::auth()->user();

                return $user?->can($permissionName) && static::canViewAny();

            }
        }

        return parent::canAccess();
    }

    public static function getAuthorizationResponse(UnitEnum|string $action, ?Model $record = null): Response
    {
        if (! static::skipAccessRightPermissionChecking()) {

            $model = $record ? get_class($record) : static::getModel();

            $id = $record != null ? $record->getKey() : null;

            $result = PermissionManifest::authorizeModel($action, $model, false, $id);

            if ($result !== null && $result === false) {
                return Response::deny();
            }
        }

        return parent::getAuthorizationResponse($action, $record);
    }
}
