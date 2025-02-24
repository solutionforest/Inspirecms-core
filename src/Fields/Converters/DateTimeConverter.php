<?php

namespace SolutionForest\InspireCms\Fields\Converters;

class DateTimeConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        if (is_null($sourceValue)) {
            return null;
        }

        if (is_string($sourceValue) && filled($sourceValue)) {

            return \Carbon\Carbon::parse($sourceValue);

        } elseif ($sourceValue instanceof \DateTimeInterface) {

            return $sourceValue;

        } else {

            return null;

        }
    }
}
