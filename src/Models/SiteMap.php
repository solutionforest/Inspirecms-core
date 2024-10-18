<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Models\Contracts\SiteMap as SiteMapContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class SiteMap extends BaseModel implements SiteMapContract
{
    protected $guarded = ['id'];

    protected $casts = [
        'enable' => 'boolean',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the URL of the sitemap.
     */
    public function getUrl(): string
    {
        if ($this->model && $this->model instanceof Contracts\Content) {
            return $this->model->getUrl();
        }

        return $this->url ?? '';
    }

    /**
     * Get the last modified date of the sitemap.
     */
    public function getLastModified(): \DateTime
    {
        return $this->{$this->getUpdatedAtColumn()};
    }

    /**
     * Get the change frequency of the sitemap.
     */
    public function getChangeFrequency(): string
    {
        return $this->change_frequency;
    }

    /**
     * Get the priority of the sitemap.
     */
    public function getPriority(): float
    {
        return $this->priority;
    }

    public function generateSitemapItem(): array
    {
        return [
            'url' => $this->getUrl(),
            'lastmod' => $this->getLastModified()->format('c'),
            'changefreq' => $this->getChangeFrequency(),
            'priority' => $this->getPriority(),
        ];
    }
}
