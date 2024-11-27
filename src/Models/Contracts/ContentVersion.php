<?php

namespace SolutionForest\InspireCms\Models\Contracts;

interface ContentVersion
{
    /**
     * Get the content associated with the content version.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo The associated content.
     */
    public function content();

    /**
     * Get the associated publish log for the content version.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function publishLog();

    /**
     * @return array
     */
    public function getDifferences();

    /**
     * Scope a query to only include published content versions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsPublished($query, bool $condition = true);
}
