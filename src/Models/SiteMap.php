<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Facades\InspireCms;
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
    public function getUrl(?string $locale = null): string
    {
        if ($this->model && $this->model instanceof Contracts\Content) {
            return $this->model->getUrl($locale);
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
        $languages = InspireCms::getAllAvailableLanguages();
        
        $urls = collect($languages)->map(function ($language) {
            return [
                'code' => $language->code,
                'locale' => $language->locale,
                'url' => $this->getUrl($language->locale),
            ];
        })->values()->all();

        if (empty($urls)) {
            return [];
        }

        return [
            'url' => $this->getUrl(),
            'urls' => $urls,
            'lastmod' => $this->getLastModified()->format('c'),
            'changefreq' => $this->getChangeFrequency(),
            'priority' => $this->getPriority(),
        ];
    }

    //regions Scope(s)
    public function scopeWhereEnabled($query, bool $condition = true)
    {
        return $query->where('enable', $condition);
    }
    //endregions Scope(s)
}
