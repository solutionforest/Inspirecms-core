<?php

namespace SolutionForest\InspireCms\Fields\Converters;

class DateTimeConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $value = $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);

        if (is_null($value)) {
            return null;
        }

        if (is_string($value) && filled($value)) {

            return \Carbon\Carbon::parse($value);

        } elseif ($value instanceof \DateTimeInterface) {

            return $value;

        } else {

            return null;

        }
    }
}
