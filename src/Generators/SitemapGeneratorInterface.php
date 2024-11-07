<?php

namespace SolutionForest\InspireCms\Generators;

interface SitemapGeneratorInterface
{
    /**
     * Generates the sitemap file.
     *
     * This method is responsible for creating the sitemap file
     * which is used by search engines to index the website's pages.
     *
     * @return void
     */
    public function generateSitemapFile(): void;
}
