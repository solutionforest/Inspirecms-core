<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\FilamentFieldGroup\Models\Contracts\Field as BaseContract;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string $type
 * @property int $group_id
 * @property int $sort
 * @property ?string $instructions
 * @property bool $mandatory
 * @property ?string $state_path
 * @property ?array $config
 * @property ?\Carbon\Carbon $created_at
 * @property ?\Carbon\Carbon $updated_at
 */
interface Field extends BaseContract, HasDtoModel
{
    /**
     * Get the configuration for the field type.
     *
     * @return array The configuration settings for the field type.
     */
    public function getFieldTypeConfigAttribute();

    /**
     * Scope a query to only include fields of a given group.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGroup($query, string $group);

    /**
     * Scope a query to include fields with a group name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithGroupName($query);
}
