<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\EloquentSortable\Sortable;

interface ComponentFieldGroup extends Sortable
{
    /**
     * Get the field group associated with the component field group.
     *
     * This method should return a BelongsTo relationship
     * representing the field group linked to the component field group.
     *
     * @return BelongsTo The associated field group.
     */
    public function fieldGroup(): BelongsTo;

    /**
     * Get the model associated with the component field group.
     *
     * This method should return a MorphTo relationship
     * representing the model linked to the component field group.
     *
     * @return MorphTo The associated model.
     */
    public function model(): MorphTo;
}
