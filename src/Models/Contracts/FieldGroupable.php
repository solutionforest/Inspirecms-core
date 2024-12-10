<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;

/**
 * @property int $id
 * @property int $field_group_id
 * @property int $groupable_id
 * @property string $groupable_type
 * @property int $inherited_from_id
 * @property string $inherited_from_type
 * 
 * @property null | Model & FieldGroup $fieldGroup
 * @property null | Model $groupabled
 * @property null | Model $inheritedFrom
 */
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
