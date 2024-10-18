<?php

namespace SolutionForest\InspireCms\Factories;

use SolutionForest\InspireCms\Support\UrlGenerators\ContentUrlGeneratorInterface;

class ContentUrlGeneratorFactory
{
    public static function create(): ContentUrlGeneratorInterface
    {
        $urlGeneratorClass = config('inspirecms.generators.content_url_generator');

        static::guardAgainstInvalidUrlGenerator($urlGeneratorClass);

        return app($urlGeneratorClass);
    }

    protected static function guardAgainstInvalidUrlGenerator(string $urlGeneratorClass): void
    {
        if (! is_subclass_of($urlGeneratorClass, ContentUrlGeneratorInterface::class)) {
            throw new \InvalidArgumentException('Path generator class must implement ' . ContentUrlGeneratorInterface::class);
        }
    }
}
