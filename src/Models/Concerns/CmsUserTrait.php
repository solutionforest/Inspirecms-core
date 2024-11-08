<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Filament\Panel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Base\Enums\UserActivity;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\InspireCmsConfig;
use Spatie\Permission\Traits\HasRoles;

trait CmsUserTrait
{
    use HasRoles;
    use Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->is_active) {
            return false;
        }

        // todo add more checks
        return true;
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (filled($this->avatar)) {
            return Storage::disk(config('inspirecms.avatar.driver'))->url($this->avatar);
        }

        return null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(PermissionManifest::getSuperAdminRoleName(), InspireCmsConfig::getGuardName());
    }

    public function getDefaultGuardName(): string
    {
        return InspireCmsConfig::getGuardName();
    }

    public function handleActivity(UserActivity $activity)
    {
        switch ($activity) {
            case UserActivity::Login:

                $this->updateQuietly(['last_logged_in_at' => now()]);

                $this->userActivity()->updateOrCreate([
                    'ip_address' => request()->ip(),
                ], [
                    'last_logged_in_at_utc' => now()->utc(),
                ]);

                break;

            case UserActivity::Logout:

                $this->userActivity()->updateOrCreate([
                    'ip_address' => request()->ip(),
                ], [
                    'last_logged_out_at_utc' => now()->utc(),
                ]);

                break;

            case UserActivity::FailedLogin:

                $failedLoginAttempt = $user->failed_login_attempt ?? 0;
                $failedLoginAttempt += 1;

                if ($this->hasExceededMaxLoginAttempts($failedLoginAttempt)) {
                    $this->last_lockouted_at = now();
                }

                $this->failed_login_attempt = $failedLoginAttempt;

                $this->saveQuietly();

                break;

            case UserActivity::PasswordReset:

                $this->updateQuietly([
                    'last_password_change_date' => now(),
                ]);

                break;

            default:
                break;
        }
    }

    public function hasExceededMaxLoginAttempts($attempt): bool
    {
        $maxFailedLoginAttempt = InspireCmsConfig::get('auth.failed_login_attempts', 5);

        if ($attempt >= $maxFailedLoginAttempt) {
            return true;
        }

        return false;
    }

    //region Attributes
    public function getIsActiveAttribute()
    {
        $lockoutDuration = InspireCmsConfig::get('auth.lockout_duration', 5);

        if (! $this->hasExceededMaxLoginAttempts($this->failed_login_attempt ?? 0)) {
            return true;
        }

        // no limit
        if (is_null($this->last_lockouted_at)) {
            return false;
        }

        return $this->last_lockouted_at->addMinutes($lockoutDuration)->isPast();
    }
    //endregion Attributes

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
