<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Fields\PropertyValueTransformerInterface;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\HasFallbackLocale;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

/**
 * @extends BaseDto<PropertyDataDto>
 */
class PropertyDataDto extends BaseDto
{
    use HasFallbackLocale;

    /**
     * @var string
     */
    public $key;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var ?PropertyTypeDto
     */
    public $propertyType;

    public function getValue(?string $locale = null): mixed
    {

        $locale ??= $this->getFallbackLocale();

        try {

            $transformer = app(PropertyValueTransformerInterface::class);

            return $transformer->attemptTransform($this, $locale, $this->getFallbackLocale());

        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getSourceValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param  PropertyTypeDto  $propertyType
     * @param  string[]  $availableLocales
     */
    public static function fakeValueForPropertyType($propertyType, $availableLocales)
    {
        $fieldType = $propertyType->config;
        $value = null;

        if ($fieldType  instanceof \SolutionForest\InspireCms\Fields\Configs\Repeater) {

            $value = collect(range(1, 3))->map(
                fn ($i) => collect($fieldType->fields)->mapWithKeys(function ($field) {
                    $innerFieldType = FieldTypeHelper::getFieldTypeConfig($field['field'], $field['fieldConfig'] ?? []);

                    return [
                        $field['name'] => static::getFakeValueForBasicFieldType($innerFieldType),
                    ];
                })->toArray()
            )->all();
        } elseif ($fieldType?->isTranslatable()) {
            $value = collect($availableLocales)->mapWithKeys(function ($locale) use ($fieldType) {
                return [$locale => static::getFakeValueForBasicFieldType($fieldType)];
            })->toArray();
        } else {
            $value = static::getFakeValueForBasicFieldType($propertyType->config);
        }

        return $value;

    }

    /**
     * @param  \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig  $fieldType
     * @param  string[]  $availableLocales
     */
    protected static function getFakeValueForBasicFieldType($fieldType)
    {
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
