<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface User extends AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, FilamentUser, HasAvatar, HasName
{
    /**
     * Get the user activity associated with the user.
     *
     * This method should return a HasOne relationship
     * representing the user activity linked to the user.
     *
     * @return HasOne The associated user activity.
     */
    public function userActivity(): HasOne;

    public function isSuperAdmin(): bool;
}
