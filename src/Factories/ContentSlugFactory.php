<?php

namespace SolutionForest\InspireCms\Factories;

use SolutionForest\InspireCms\Content\DefaultSlugGenerator;
use SolutionForest\InspireCms\Content\SlugGeneratorInterface;
use SolutionForest\InspireCms\InspireCmsConfig;

class ContentSlugFactory
{
    public static function create(): SlugGeneratorInterface
    {
        $class = InspireCmsConfig::get('frontend.slug_generator', DefaultSlugGenerator::class);

        static::guardAgainstInvalidContentSlugGenerator($class);

        return app($class);
    }

    protected static function guardAgainstInvalidContentSlugGenerator(string $class): void
    {
        if (! in_array(SlugGeneratorInterface::class, class_implements($class))) {
            throw new \InvalidArgumentException('Must implement ' . SlugGeneratorInterface::class);
        }
    }
}
