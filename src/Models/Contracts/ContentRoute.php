<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $content_id
 * @property ?int $language_id
 * @property string $uri
 * @property bool $is_default_pattern
 * @property ?array $regex_constraints
 * 
 * @property-read null | Model & Content $content
 * @property-read null | Model & Language $language
 */
interface ContentRoute
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language();

    /**
     * Scope a query to only include routes that are the default pattern.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $condition
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsDefaultPattern($query, bool $condition = true);
}
