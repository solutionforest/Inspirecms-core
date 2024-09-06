<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Base\Interfaces\NestableInterface;
use Spatie\EloquentSortable\Sortable;

interface NestableTree extends NestableInterface, Sortable
{
    /**
     * Get the nestable relationship for the nestable tree.
     *
     * @return MorphTo The nestable relationship.
     */
    public function nestable(): MorphTo;
}
