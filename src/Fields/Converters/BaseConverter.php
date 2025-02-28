<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Support\Helpers\TranslatableHelper;

abstract class BaseConverter
{
    protected FieldTypeConfig $fieldTypeConfig;

    public function __construct(FieldTypeConfig $fieldTypeConfig)
    {
        $this->fieldTypeConfig = $fieldTypeConfig;
    }

    abstract public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale);

    protected function applyLocaleConversion(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        if ($this->isFieldTypeTranslatable()) {
            return TranslatableHelper::getTranslations(
                $sourceValue,
                $locale ?? $fallbackLocale,
                $fallbackLocale
            );
        }

        return $sourceValue;
    }

    protected function isFieldTypeTranslatable(): bool
    {
        return $this->fieldTypeConfig->isTranslatable();
    }
}
