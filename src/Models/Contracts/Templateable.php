<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $templateable_type
 * @property string $templateable_id
 * @property int $template_id
 * @property bool $is_default
 */
interface Templateable
{
    /**
     * Get the templateable entity.
     *
     * @return MorphTo The templateable entity.
     */
    public function templateable(): MorphTo;
    
    /**
     * Scope a query to only include default templates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsDefault($query);
}
