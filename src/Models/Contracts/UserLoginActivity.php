<?php

namespace SolutionForest\InspireCms\Models\Contracts;

interface UserLoginActivity
{
    /**
     * Define a one-to-many relationship with the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users();
}
