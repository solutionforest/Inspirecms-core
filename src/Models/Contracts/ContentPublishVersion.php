<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $content_id
 * @property int $version_id
 * @property Carbon $published_at
 * @property-read null | Model & Content $content
 * @property-read null | Model & ContentVersion $version
 */
interface ContentPublishVersion
{
    /**
     * Get the content associated with the publish version.
     *
     * @return BelongsTo
     */
    public function content();

    /**
     * Get the version associated with the content.
     *
     * @return BelongsTo
     */
    public function version();

    /**
     * Scope a query to only include published content.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereIsPublished($query);
}
