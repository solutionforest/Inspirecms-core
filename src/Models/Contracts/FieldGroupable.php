<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Spatie\EloquentSortable\Sortable;

interface FieldGroupable extends Sortable
{
    /**
     * Get the field group that this model belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fieldGroup();

    /**
     * Get the morphable relationship for the groupable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function groupabled();

    /**
     * Get the morphable relationship for the inherited model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function inheritedFrom();
}
