<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\InspireCms\Support\Models\Contracts\HasAuthor;

/**
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property string $event_name
 * @property string $publish_state
 * @property string $content_id
 * @property array $from_data
 * @property array $to_data
 * @property bool $avoid_to_clean
 */
interface ContentVersion extends HasAuthor
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
