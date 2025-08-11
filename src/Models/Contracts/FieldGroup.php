<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\FilamentFieldGroup\Models\Contracts\FieldGroup as BaseContract;

/**
 * @property int $id
 * @property string $title
 * @property string $name
 * @property bool $active
 * @property int $sort
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $updated_at
 */
interface FieldGroup extends BaseContract
{
    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @return MorphToMany
     */
    public function documentTypes();

    /**
     * @return HasMany
     */
    public function groupabled();

    /**
     * Scope a query to only include active records.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereActive($query, bool $condition = true);
}
