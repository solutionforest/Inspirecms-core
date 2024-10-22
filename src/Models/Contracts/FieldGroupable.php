<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\EloquentSortable\Sortable;

interface FieldGroupable extends Sortable
{
    /**
     * Get the field group associated with the component field group.
     *
     * @return BelongsTo The associated field group.
     */
    public function fieldGroup(): BelongsTo;

    /**
     * Get the model associated with the field groupable.
     *
     * @return MorphTo The associated model.
     */
    public function groupabled(): MorphTo;

    /**
     * Define a polymorphic relationship for inherited models.
     */
    public function inheritedFrom(): MorphTo;
}
