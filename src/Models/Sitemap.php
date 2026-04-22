<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\Base\Enums\SitemapChangeFrequency;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\Sitemap as SitemapContract;
use SolutionForest\InspireCms\Observers\SitemapObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class Sitemap extends BaseModel implements SitemapContract
{
    protected $guarded = ['id'];

    protected $casts = [
        'enable' => 'boolean',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function getType()
    {
        return $this->model_type ?? 'general';
    }

    public function getUrl($locale = null)
    {
        if ($this->model && $this->model instanceof Content) {
            return $this->model->getUrl($locale) ?? '';
        }

        return $this->url ?? '';
    }

    public function getLastModified()
    {
        return $this->{$this->getUpdatedAtColumn()};
    }

    public function getChangeFrequency()
    {
        return (SitemapChangeFrequency::tryFrom($this->change_frequency) ?? SitemapChangeFrequency::Monthly)->value;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    protected function isAllowIndex(): bool
    {
        if (! $this->enable) {
            return false;
        }

        if ($this->model && $this->model instanceof Content) {
            return $this->model->isAllowIndex();
        }

        return true;
    }

    public function generateSitemapItem(): array
    {
        $languages = InspireCms::getAllAvailableLanguages();

        $urls = collect($languages)->map(function ($language) {
            return [
                'code' => $language->code,
                'url' => $this->getUrl($language->code),
            ];
        })->values()->all();

        if (empty($urls)) {
            return [];
        }

        if (! $this->isAllowIndex()) {
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

    // regions Scope(s)
    public function scopeWhereEnabled($query, bool $condition = true)
    {
        return $query->where('enable', $condition);
    }
    // endregions Scope(s)

    public function setDisable(bool $save = true)
    {
        $this->enable = false;
        if ($save) {
            $this->save();
        }
    }

    public function setEnable(bool $save = true)
    {
        $this->enable = true;
        if ($save) {
            $this->save();
        }
    }

    public static function booted()
    {
        parent::booted();

        static::observe(SitemapObserver::class);
    }
}
