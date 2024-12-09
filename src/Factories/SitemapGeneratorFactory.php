<?php

namespace SolutionForest\InspireCms\Factories;

use SolutionForest\InspireCms\Generators\SitemapGeneratorInterface;
use SolutionForest\InspireCms\InspireCmsConfig;

class SitemapGeneratorFactory
{
    public static function create(): SitemapGeneratorInterface
    {
        $sitemapGeneratorClass = InspireCmsConfig::get('generators.sitemap_generator');

        static::guardAgainstInvalidSitemapGenerator($sitemapGeneratorClass);

        return app($sitemapGeneratorClass);
    }

    protected static function guardAgainstInvalidSitemapGenerator(string $sitemapGeneratorClass): void
    {
        if (! is_subclass_of($sitemapGeneratorClass, SitemapGeneratorInterface::class)) {
            throw new \InvalidArgumentException('Sitemap generator class must implement ' . SitemapGeneratorInterface::class);
        }
    }
}
