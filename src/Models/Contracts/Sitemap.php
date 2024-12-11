<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
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
 * @property ?\Carbon\Carbon $created_at
 * @property ?\Carbon\Carbon $updated_at
 * @property null | Model $model
 */
interface Sitemap extends ActivableEntity, HasLocaleUrl
{
    /**
     * Get the model that this sitemap belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
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
     * @return \DateTime
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
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereEnabled($query, bool $condition = true);
}
