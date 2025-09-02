<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SolutionForest\InspireCms\Base\Enums\UserActivity;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $preferred_language
 * @property ?string $avatar
 * @property int $failed_login_attempt
 * @property ?CarbonInterface $last_lockouted_at
 * @property ?CarbonInterface $last_password_change_date
 * @property ?CarbonInterface $last_logged_in_at
 * @property ?CarbonInterface $email_confirmed_at
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $updated_at
 * @property-read bool $is_locked
 * @property-read ?CarbonInterface $locked_until
 */
interface User extends AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, FilamentUser, HasAvatar, HasName, MustVerifyEmail
{
    /**
     * Get the user's activity.
     *
     * @return HasMany
     */
    public function userActivities();

    public function getFilamentFallbackAvatarUrl(): ?string;

    /**
     * Determine if the user is a super admin.
     *
     * @return bool True if the user is a super admin, false otherwise.
     */
    public function isSuperAdmin();

    /**
     * Handle the given user activity.
     *
     * @param  UserActivity  $activity  The user activity to handle.
     * @return void
     */
    public function handleActivity($activity);

    /**
     * Determine if the user has exceeded the maximum number of login attempts.
     *
     * @param  int  $attempt  The current number of login attempts.
     * @return bool True if the maximum number of login attempts has been exceeded, false otherwise.
     */
    public function hasExceededMaxLoginAttempts($attempt): bool;
}
