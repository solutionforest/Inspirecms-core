<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Filament\Panel;
use Illuminate\Notifications\Notifiable;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use Spatie\Permission\Traits\HasRoles;

trait CmsUserTrait
{
    use HasRoles;
    use Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        // Implement the logic to determine if the user can access the panel
        return true; // Placeholder logic
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(PermissionManifest::getSuperAdminRoleName(), InspireCmsConfig::getGuardName());
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            // Fill "preferred_language" if empty
            if (blank($model->preferred_language)) {
                $model->preferred_language = config('app.locale') ?? app()->getLocale();
            }
        });
    }
}
