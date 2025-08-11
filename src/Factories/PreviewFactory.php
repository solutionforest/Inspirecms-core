<?php

namespace SolutionForest\InspireCms\Factories;

use InvalidArgumentException;
use SolutionForest\InspireCms\Content\DefaultPreviewProvider;
use SolutionForest\InspireCms\Content\PreviewProviderInterface;
use SolutionForest\InspireCms\InspireCmsConfig;

class PreviewFactory
{
    public static function create(): PreviewProviderInterface
    {
        $class = InspireCmsConfig::get('frontend.preview_provider', DefaultPreviewProvider::class);

        static::guardAgainstInvalidPreviewGenerator($class);

        return app($class);
    }

    protected static function guardAgainstInvalidPreviewGenerator(string $class): void
    {
        if (! in_array(PreviewProviderInterface::class, class_implements($class))) {
            throw new InvalidArgumentException('Must implement ' . PreviewProviderInterface::class);
        }
    }
}
