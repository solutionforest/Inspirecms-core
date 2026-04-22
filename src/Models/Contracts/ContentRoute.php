<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $content_id
 * @property ?int $language_id
 * @property string $uri
 * @property bool $is_default_pattern
 * @property ?array $regex_constraints
 * @property-read null | Model & Content $content
 * @property-read null | Model & Language $language
 */
interface ContentRoute
{
    /**
     * @return BelongsTo
     */
    public function content();

    /**
     * @return BelongsTo
     */
    public function language();

    /**
     * Scope a query to only include routes that are the default pattern.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereIsDefaultPattern($query, bool $condition = true);
}
