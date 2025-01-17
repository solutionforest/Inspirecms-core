<?php

namespace SolutionForest\InspireCms\Factories;

use SolutionForest\InspireCms\Content\SegmentProviderInterface;
use SolutionForest\InspireCms\InspireCmsConfig;

class ContentSegmentFactory
{
    public static function create(): SegmentProviderInterface
    {
        $class = InspireCmsConfig::get('content.segment_provider');

        static::guardAgainstInvalidContentUrlSegmentGenerator($class);

        return app($class);
    }

    protected static function guardAgainstInvalidContentUrlSegmentGenerator(string $class): void
    {
        if (! in_array(SegmentProviderInterface::class, class_implements($class))) {
            throw new \InvalidArgumentException('Must implement ' . SegmentProviderInterface::class);
        }
    }
}
