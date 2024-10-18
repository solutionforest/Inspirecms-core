<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface SiteMap
{
    public function model(): MorphTo;

    /**
     * Get the URL of the sitemap.
     */
    public function getUrl(): string;

    /**
     * Get the last modified date of the sitemap.
     */
    public function getLastModified(): \DateTime;

    /**
     * Get the change frequency of the sitemap.
     */
    public function getChangeFrequency(): string;

    /**
     * Get the priority of the sitemap.
     */
    public function getPriority(): float;

    /**
     * Generate the sitemap item.
     *
     * @return {url: string, lastmod: string, changefreq: string, priority: string}
     */
    public function generateSitemapItem(): array;
}
