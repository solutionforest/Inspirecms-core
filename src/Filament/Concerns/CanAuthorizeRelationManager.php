<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\PermissionManifest;

trait CanAuthorizeRelationManager
{
    public static function skipAccessRightPermissionChecking(): bool
    {
        return config('inspirecms.skip_access_right_permission_on_resource', false);
    }

    protected function can(string $action, ?Model $record = null): bool
    {
        if (! static::skipAccessRightPermissionChecking()) {

            $model = $record ? get_class($record) : $this->getTable()->getModel();

            $result = PermissionManifest::authorizeModel($action, $model);

            if ($result !== null) {
                return $result;
            }

        }

        return parent::can($action, $record);
    }
}
