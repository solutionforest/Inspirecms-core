<?php

namespace SolutionForest\InspireCms\Factories;

use SolutionForest\InspireCms\Sitemap\SitemapGeneratorInterface;
use SolutionForest\InspireCms\InspireCmsConfig;

class SitemapGeneratorFactory
{
    public static function create(): SitemapGeneratorInterface
    {
        $class = InspireCmsConfig::get('sitemap.generator');

        static::guardAgainstInvalidSitemapGenerator($class);

        return app($class);
    }

    protected static function guardAgainstInvalidSitemapGenerator(string $class): void
    {
        if (! in_array(SitemapGeneratorInterface::class, class_implements($class))) {
            throw new \InvalidArgumentException('Must implement ' . SitemapGeneratorInterface::class);
        }
    }
}
