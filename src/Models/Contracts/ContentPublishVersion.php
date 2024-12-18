<?php

namespace SolutionForest\InspireCms\Models\Contracts;

/**
 * @property string $content_id
 * @property int $version_id
 * @property \Carbon\Carbon $published_at
 * @property-read null | Model & Content $content
 * @property-read null | Model & ContentVersion $version
 */
interface ContentPublishVersion
{
    /**
     * Get the content associated with the publish version.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content();

    /**
     * Get the version associated with the content.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function version();

    /**
     * Scope a query to only include published content.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsPublished($query);
}
