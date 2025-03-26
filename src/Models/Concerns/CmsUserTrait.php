<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Base\Enums\UserActivity;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use Spatie\Permission\Traits\HasRoles;

trait CmsUserTrait
{
    use HasRoles;
    use Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function isAccountVerified(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->hasVerifiedEmail();
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (filled($this->avatar)) {
            return Storage::disk(InspireCmsConfig::get('avatar.driver'))->url($this->avatar);
        }

        return null;
    }

    public function getFilamentFallbackAvatarUrl(): ?string
    {
        if (($providerClass = filament()->getCurrentPanel()?->getDefaultAvatarProvider())
            && class_exists($providerClass)
            && is_a($providerClass, AvatarProvider::class, true)
        ) {
            return app($providerClass)->get($this);
        }

        return null;
    }

    public function isSuperAdmin()
    {
        return $this->hasRole(PermissionManifest::getSuperAdminRoleName(), AuthHelper::guardName());
    }

    public function getDefaultGuardName(): string
    {
        return AuthHelper::guardName();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function handleActivity($activity)
    {
        switch ($activity) {
            case UserActivity::Login:

                $this->updateQuietly([
                    'last_logged_in_at' => now(),

                    // Reset failed login attempt count
                    'failed_login_attempt' => 0,
                    'last_lockouted_at' => null,
                ]);

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

                $failedLoginAttempt = intval($this->failed_login_attempt ?? 0);
                $failedLoginAttempt++;

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

            case UserActivity::LockoutReset:

                $this->updateQuietly([
                    'failed_login_attempt' => 0,
                    'last_lockouted_at' => null,
                ]);

                break;

            default:
                break;
        }
    }

    public function hasExceededMaxLoginAttempts($attempt): bool
    {
        $maxFailedLoginAttempt = AuthHelper::maxAttempts();

        if (intval($attempt) >= $maxFailedLoginAttempt) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        if (AuthHelper::skipAccountVerification()) {
            return true;
        }

        return ! is_null($this->email_confirmed_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_confirmed_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    public function sendEmailVerificationNotification()
    {
        $notification = app(VerifyEmail::class);
        $notification->url = Filament::getVerifyEmailUrl($this);

        $this->notify($notification);
    }

    // region Attributes
    public function lockedUntil(): Attribute
    {
        return new Attribute(
            get: function () {
                if (is_null($this->last_lockouted_at)) {
                    return null;
                }

                return $this->last_lockouted_at->addMinutes(InspireCmsConfig::get('auth.lockout_duration', 5));
            },
        );
    }

    public function isLocked(): Attribute
    {
        return new Attribute(
            get: function () {

                if (is_null($this->locked_until)) {
                    return false;
                } else {
                    return now()->lessThan($this->locked_until);
                }

                $currentAttempt = $this->failed_login_attempt ?? 0;

                return $this->hasExceededMaxLoginAttempts($currentAttempt);

            },
        );
    }
    // endregion Attributes

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
