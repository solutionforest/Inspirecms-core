<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use SolutionForest\InspireCms\Base\Enums\UserActivity;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $preferred_language
 * @property ?string $avatar
 * @property int $failed_login_attempt
 * @property ?\Carbon\CarbonInterface $last_lockouted_at
 * @property ?\Carbon\CarbonInterface $last_password_change_date
 * @property ?\Carbon\CarbonInterface $last_logged_in_at
 * @property ?\Carbon\CarbonInterface $email_confirmed_at
 * @property ?\Carbon\CarbonInterface $created_at
 * @property ?\Carbon\CarbonInterface $updated_at
 * 
 * @property-read bool $is_active
 */
interface User extends AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, FilamentUser, HasAvatar, HasName
{
    /**
     * Get the user's activity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userActivity();

    public function getFilamentFallbackAvatarUrl(): ?string;

    /**
     * Check if the user account is verified.
     *
     * @return bool 
     */
    public function isAccountVerified(): bool;

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
