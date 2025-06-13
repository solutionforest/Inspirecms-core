<?php

namespace SolutionForest\InspireCms\Helpers;

use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\ColorPicker;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\DateTimePicker;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Email;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\File;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Number;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Password;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Radio;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Select;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Text;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Textarea;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Toggle;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Url;
use SolutionForest\InspireCms\Fields\Configs\ContentPicker;
use SolutionForest\InspireCms\Fields\Configs\MarkdownEditor;
use SolutionForest\InspireCms\Fields\Configs\MediaPicker;
use SolutionForest\InspireCms\Fields\Configs\Repeater;
use SolutionForest\InspireCms\Fields\Configs\RichEditor;
use SolutionForest\InspireCms\Fields\Configs\Tags;
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
        if ($fieldType  instanceof Repeater) {

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
            $fieldType instanceof ColorPicker => '#000000',

            $fieldType instanceof DateTimePicker => fake()->dateTime(),

            $fieldType instanceof Email => fake()->email(),

            $fieldType instanceof File,
            $fieldType instanceof Image => $fieldType->multiple
                ? collect(range(1, 3))->map(fn () => fake()->filePath())->values()->toArray()
                : fake()->filePath(),

            $fieldType instanceof Number => 123,

            $fieldType instanceof Password => 'password',

            $fieldType instanceof Radio => array_key_first($fieldType->options),

            $fieldType instanceof Select => $fieldType->multiple
                ? collect($fieldType->options)->take(3)->keys()->toArray()
                : array_key_first($fieldType->options),

            $fieldType instanceof Text => 'Lorem ipsum dolor sit amet',

            $fieldType instanceof Textarea => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',

            $fieldType instanceof Toggle => fake()->boolean(),

            $fieldType instanceof Url => 'https://example.com',

            $fieldType instanceof MediaPicker,
            $fieldType instanceof ContentPicker => [KeyHelper::generateMinUuid()],

            $fieldType instanceof MarkdownEditor => 'Lorem **ipsum** dolor sit amet, consectetur adipiscing <em>elit</em>.',
            $fieldType instanceof RichEditor => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p><p>Curabitur non nulla sit amet nisl <b>tempus</b> convallis quis ac lectus.</p>',

            $fieldType instanceof Tags => ['tag1', 'tag2'],

            default => null,
        };
    }
}
