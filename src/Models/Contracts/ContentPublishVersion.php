<?php

namespace SolutionForest\InspireCms\Models\Contracts;

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
