<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Templateable
{
    public function templateable(): MorphTo;
}
