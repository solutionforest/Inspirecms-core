<?php

namespace SolutionForest\InspireCms\Fields\Converters;

class DefaultConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        return $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);
    }
}
