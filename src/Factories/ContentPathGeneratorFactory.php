<?php

namespace SolutionForest\InspireCms\Factories;

use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\PathGenerators\ContentPathGeneratorInterface;

class ContentPathGeneratorFactory
{
    public static function createFor(Content $content): ContentPathGeneratorInterface
    {
        $pathGeneratorClass = config('inspirecms.generators.content_path_generator');

        static::guardAgainstInvalidPathGenerator($pathGeneratorClass);

        return app($pathGeneratorClass, ['content' => $content]);
    }

    protected static function guardAgainstInvalidPathGenerator(string $pathGeneratorClass): void
    {
        if (! is_subclass_of($pathGeneratorClass, ContentPathGeneratorInterface::class)) {
            throw new \InvalidArgumentException('Path generator class must implement ' . ContentPathGeneratorInterface::class);
        }
    }
}
