<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\FilamentFieldGroup\Models\Contracts\FieldGroup as BaseContract;

/**
 * @property string $title
 * @property string $name
 * @property bool $active
 * @property int $sort
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
interface FieldGroup extends BaseContract
{
    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function documentTypes();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupabled();

    /**
     * Scope a query to only include active records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereActive($query, bool $condition = true);
}
