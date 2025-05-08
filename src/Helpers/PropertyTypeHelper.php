<?php

namespace SolutionForest\InspireCms\Helpers;

use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

class PropertyTypeHelper
{
    /**
     * @param  FieldTypeConfig  $fieldType
     */
    public static function getFieldDisplayValueType($fieldType)
    {
        $displayValue = static::fakeDisplayValueForFieldType($fieldType);

        return gettype($displayValue);
    }

    /**
     * @param  PropertyTypeDto  $propertyType
     * @param  string[]  $availableLocales
     */
    public static function fakeDisplayValueForPropertyType($propertyType, $availableLocales)
    {
        $fieldType = $propertyType->config;
        $value = null;

        if ($fieldType?->isTranslatable()) {
            $value = collect($availableLocales)
                ->mapWithKeys(fn ($locale) => [$locale => static::fakeDisplayValueForFieldType($fieldType)])
                ->toArray();
        } else {
            $value = static::fakeDisplayValueForFieldType($propertyType->config);
        }

        return $value;
    }

    /**
     * @param  FieldTypeConfig  $fieldType
     */
    public static function fakeDisplayValueForFieldType($fieldType)
    {
        if ($fieldType  instanceof \SolutionForest\InspireCms\Fields\Configs\Repeater) {

            return collect(range(1, 3))
                ->map(
                    fn ($i) => collect($fieldType->fields)
                        ->mapWithKeys(function ($field) {
                            $innerFieldType = FieldTypeHelper::getFieldTypeConfig($field['field'], $field['fieldConfig'] ?? []);

                            return [
                                $field['name'] => static::fakeDisplayValueForFieldType($innerFieldType),
                            ];
                        })
                        ->toArray()
                )
                ->all();
        }

        return match (true) {
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\ColorPicker => '#000000',

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\DateTimePicker => fake()->dateTime(),

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Email => fake()->email(),

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\File,
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image => $fieldType->multiple
                ? collect(range(1, 3))->map(fn () => fake()->filePath())->values()->toArray()
                : fake()->filePath(),

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Number => 123,

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Password => 'password',

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Radio => array_key_first($fieldType->options),

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Select => $fieldType->multiple
                ? collect($fieldType->options)->take(3)->keys()->toArray()
                : array_key_first($fieldType->options),

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Text => 'Lorem ipsum dolor sit amet',

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Textarea => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Toggle => fake()->boolean(),

            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Url => 'https://example.com',

            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\MediaPicker,
            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\ContentPicker => [KeyHelper::generateMinUuid()],

            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor => 'Lorem **ipsum** dolor sit amet, consectetur adipiscing <em>elit</em>.',
            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\RichEditor => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p><p>Curabitur non nulla sit amet nisl <b>tempus</b> convallis quis ac lectus.</p>',

            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\Tags => ['tag1', 'tag2'],

            default => null,
        };
    }
}
