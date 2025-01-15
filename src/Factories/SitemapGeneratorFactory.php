<?php

namespace SolutionForest\InspireCms\Factories;

use SolutionForest\InspireCms\Generators\Interfaces\SitemapGenerator;
use SolutionForest\InspireCms\InspireCmsConfig;

class SitemapGeneratorFactory
{
    public static function create(): SitemapGenerator
    {
        $sitemapGeneratorClass = InspireCmsConfig::get('generators.sitemap_generator');

        static::guardAgainstInvalidSitemapGenerator($sitemapGeneratorClass);

        return app($sitemapGeneratorClass);
    }

    protected static function guardAgainstInvalidSitemapGenerator(string $sitemapGeneratorClass): void
    {
        if (! in_array(SitemapGenerator::class, class_implements($sitemapGeneratorClass))) {
            throw new \InvalidArgumentException('Sitemap generator class must implement ' . SitemapGenerator::class);
        }
    }
}
