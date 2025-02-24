<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use Illuminate\Support\Arr;

class RichEditorConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $value = $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);

        if (! $value) {
            return $value;
        }

        if (!$this->isFieldTypeTranslatable() && is_array($value)) {
            $value = Arr::first($value);
        }

        if (is_array($value)) {
            return Arr::map($value, function ($item) {
                return $this->convertHtml($item);
            });
        }

        return $this->convertHtml($value);
    }

    private function convertHtml($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        return str($value)->toHtmlString();
    }
}
