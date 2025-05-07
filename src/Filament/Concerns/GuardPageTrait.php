<?php

namespace SolutionForest\InspireCms\Filament\Concerns;

use Filament\Facades\Filament;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;

trait GuardPageTrait
{
    public static function canAccess(): bool
    {
        $inplements = class_implements(static::class);

        $permissionsToCheck = [];

        if (in_array(GuardPage::class, $inplements)) {

            $permissionsToCheck[] = static::getPermissionName();
        }

        foreach ($permissionsToCheck as $permissionName) {

            $user = Filament::auth()->user();

            if (blank($permissionName) || ! $user) {
                continue;
            }

            if (! $user->can($permissionName)) {
                return false;
            }
        }

        return parent::canAccess();
    }
}
