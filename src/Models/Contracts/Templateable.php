<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Templateable
{
    /**
     * Get the templateable entity.
     *
     * @return MorphTo The templateable entity.
     */
    public function templateable(): MorphTo;
}
