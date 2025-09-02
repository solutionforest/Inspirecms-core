<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Base\Models\Interfaces\ActivableEntity;
use SolutionForest\InspireCms\Base\Models\Interfaces\HasLocaleUrl;

/**
 * @property int $id
 * @property string $model_type
 * @property string $model_id
 * @property ?string $url
 * @property string $change_frequency
 * @property int $priority
 * @property bool $enable
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $updated_at
 * @property-read null | Model $model
 */
interface Sitemap extends ActivableEntity, HasLocaleUrl
{
    /**
     * Get the model that this sitemap belongs to.
     *
     * @return MorphTo
     */
    public function model();

    /**
     * Get the type of the sitemap.
     *
     * @return string The type of the sitemap.
     */
    public function getType();

    /**
     * Get the last modified date of the sitemap.
     *
     * @return DateTime
     */
    public function getLastModified();

    /**
     * Get the change frequency of the sitemap.
     *
     * @return string
     */
    public function getChangeFrequency();

    /**
     * Get the priority of the sitemap.
     *
     * @return float
     */
    public function getPriority();

    /**
     * Generate the sitemap item.
     *
     * @return {url: string, lastmod: string, changefreq: string, priority: string}
     */
    public function generateSitemapItem(): array;

    /**
     * Scope a query to only include enabled items.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereEnabled($query, bool $condition = true);
}
