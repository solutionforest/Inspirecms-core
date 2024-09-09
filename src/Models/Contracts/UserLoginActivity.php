<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface UserLoginActivity
{
    /**
     * Get the users associated with the login activity.
     *
     * @return HasMany The associated users.
     */
    public function users(): HasMany;
}
