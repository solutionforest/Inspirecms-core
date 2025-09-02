<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\CanPrunable;
use SolutionForest\InspireCms\Support\Models\Contracts\HasAuthor;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property string $event_name
 * @property string $publish_state
 * @property string $content_id
 * @property array $from_data
 * @property array $to_data
 * @property bool $avoid_to_clean
 * @property-read null | Model & Content $content
 * @property-read null | Model & ContentPublishVersion $publishLog
 */
interface ContentVersion extends CanPrunable, HasAuthor
{
    /**
     * Get the content associated with the content version.
     *
     * @return BelongsTo The associated content.
     */
    public function content();

    /**
     * Get the associated publish log for the content version.
     *
     * @return HasOne
     */
    public function publishLog();

    /**
     * @return array
     */
    public function getDifferences();

    /**
     * Get the attributes to check for versioning differences.
     *
     * @return array
     */
    public function getVersioningCheckDiffData();

    /**
     * Scope a query to only include published content versions.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereIsPublished($query, bool $condition = true);
}
