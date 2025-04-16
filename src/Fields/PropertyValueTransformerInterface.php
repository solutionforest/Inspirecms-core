<?php

namespace SolutionForest\InspireCms\Fields;

use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Dtos\PropertyDataDto;
use SolutionForest\InspireCms\Fields\Converters\BaseConverter;

interface PropertyValueTransformerInterface
{
    public function transform(PropertyDataDto $propertyDataDto, ?string $locale, ?string $fallbackLocale);

    public function attemptTransform(PropertyDataDto $propertyDataDto, ?string $locale, ?string $fallbackLocale);

    /**
     * Get the converter for the specified field type.
     *
     * @param  ?FieldTypeConfig  $fieldType  The type of the field for which the converter is needed.
     * @param ?string $key The key of the field.
     * @param ?string $group The group of the field.
     * 
     * @return BaseConverter The converter for the specified field type.
     *
     * @throws \InvalidArgumentException If no field type is specified, or if no converter is found for the specified field type.
     */
    public function getConverter($fieldType, $key, $group);
}
