<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface SiteMap
{
    public function model(): MorphTo;

    public function getType(): string;

    /**
     * Get the URL of the sitemap.
     */
    public function getUrl(?string $locale = null): string;

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

    public function setDisable(bool $save = true): void;

    public function setEnable(bool $save = true): void;
}
