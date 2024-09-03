<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Base\Interfaces\NestableInterface;
use Spatie\EloquentSortable\Sortable;

interface ComponentTree extends NestableInterface, Sortable
{
    /**
     * Get the nestable relationship for the component tree.
     *
     * This method should return a MorphTo relationship
     * representing the nestable entity associated with the component tree.
     *
     * @return MorphTo The nestable relationship.
     */
    public function nestable(): MorphTo;
}
