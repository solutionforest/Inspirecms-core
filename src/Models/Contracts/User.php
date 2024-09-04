<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface User extends AuthorizableContract, AuthenticatableContract, CanResetPasswordContract
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
}
