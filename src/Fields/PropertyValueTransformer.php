<?php

namespace SolutionForest\InspireCms\Fields;

use Exception;
use InvalidArgumentException;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\DateTimePicker;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\File;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image;
use SolutionForest\InspireCms\Dtos\PropertyDataDto;
use SolutionForest\InspireCms\Fields\Converters\BaseConverter;
use SolutionForest\InspireCms\Fields\Converters\DateTimeConverter;
use SolutionForest\InspireCms\Fields\Converters\FileConverter;

class PropertyValueTransformer implements PropertyValueTransformerInterface
{
    public function transform(PropertyDataDto $propertyDataDto, ?string $locale, ?string $fallbackLocale)
    {
        $propType = $propertyDataDto->propertyType;

        $converter = $this->getConverter($propType?->config, $propType?->key, $propType?->group);

        return $converter->toDisplayValue($propertyDataDto->getSourceValue(), $locale, $fallbackLocale);
    }

    public function attemptTransform(PropertyDataDto $propertyDataDto, ?string $locale, ?string $fallbackLocale)
    {
        try {
            return $this->transform($propertyDataDto, $locale, $fallbackLocale);
        } catch (Exception $e) {

            logger()->warning('Failed to transform property data', [
                'exception' => $e,
                'propertyData' => $propertyDataDto,
            ]);

            return null;
        }
    }

    public function getConverter($fieldType, $key, $group)
    {
        if ($fieldType === null) {
            throw new InvalidArgumentException('No field type specified.');
        }

        $converter = match (true) {
            $fieldType instanceof DateTimePicker => DateTimeConverter::class,
            $fieldType instanceof Image => FileConverter::class,
            $fieldType instanceof File => FileConverter::class,
            default => $fieldType->getConverter(),
        };

        if ($converter === null) {
            throw new InvalidArgumentException('No converter found for field type ' . get_class($fieldType));
        }

        if (! is_subclass_of($converter, BaseConverter::class)) {
            throw new InvalidArgumentException('Converter for field type ' . get_class($fieldType) . ' must be an instance of ' . BaseConverter::class);
        }

        return app($converter, [
            'fieldTypeConfig' => $fieldType,
            'key' => $key,
            'group' => $group,
        ]);
    }
}
