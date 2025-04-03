<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use SolutionForest\InspireCms\Collection\ContentCollection;

class ContentPickerConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        // todo: improve performance
        $contentItems = inspirecms_content()
            ->findByIds(
                ids: $sourceValue,
                isPublished: true,
                limit: count($sourceValue),
            )
            ->filter(fn ($c) => in_array($c->getKey(), $sourceValue))
            ->sortBy(fn ($c) => array_search($c->getKey(), $sourceValue))
            ->values()
            ->map(fn ($item) => $item->toDto($locale)->setFallbackLocale($fallbackLocale))
            ->reject(fn ($item) => is_null($item));

        if (! $contentItems instanceof ContentCollection) {
            $contentItems = ContentCollection::make($contentItems->values());
        }

        return $contentItems;
    }
}
