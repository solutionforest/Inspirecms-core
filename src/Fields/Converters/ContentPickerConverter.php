<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use SolutionForest\InspireCms\Collection\ContentCollection;

class ContentPickerConverter extends BaseConverter
{
	public function toDisplayValue(mixed $sourceValue, string|null $locale, string|null $fallbackLocale)
	{
        // todo: improve performance
        $content = inspirecms_content()->findPublishedContentByIds($sourceValue)
            ->filter(fn ($c) => in_array($c->getKey(), $sourceValue))
            ->sortBy(fn ($c) => array_search($c->getKey(), $sourceValue))
            ->values();

        if ($content instanceof ContentCollection) {
            $content = $content->toDto($locale);
        } else {
            $content = ContentCollection::make(
                $content
                    ->map(fn ($c) => $c->toDto($locale))
                    ->values()
            );
        }

        return $content;
	}
}
