<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use SolutionForest\InspireCms\Base\Enums\UserActivity;

interface User extends AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, FilamentUser, HasAvatar, HasName
{
    /**
     * Get the user's activity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userActivity();

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
