<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasOne;

interface User
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
