<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\HtmlString;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Fields\Dtos\FileDto;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\HasFallbackLocale;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Helpers\TranslatableHelper;

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
        $propertyType = $this->propertyType?->config;

        $propertyDataValue = $this->getSourceValue();

        $locale ??= $this->getFallbackLocale();

        try {
            return $this->transformPropertyValueWithTranslatable($propertyDataValue, $propertyType, $locale);
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getSourceValue(): mixed
    {
        return $this->value;
    }

    protected function transformPropertyValueWithTranslatable($sourceValue, ?FieldTypeConfig $propertyType, ?string $locale)
    {
        if (is_null($propertyType)) {
            return $sourceValue;
        }

        if ($propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\Repeater) {

            return collect($sourceValue)->map(function ($data, $i) use ($propertyType, $locale) {

                $result = [];
                $propertyTypes = [];

                foreach ($data as $key => $value) {

                    $field = collect($propertyType->fields)->firstWhere('name', $key);

                    if (is_null($field) || ! isset($field['field']) || blank($field['field'])) {
                        continue;
                    }

                    $innerPropertyType = FieldTypeHelper::getFieldTypeConfig($field['field'], $field['fieldConfig'] ?? []);

                    if (is_null($innerPropertyType)) {
                        continue;
                    }

                    $finalValue = $this->transformPropertyValueWithTranslatable($value, $innerPropertyType, $locale);

                    $result[] = PropertyDataDto::fromArray([
                        'key' => $key,
                        'value' => $finalValue,
                        'propertyType' => PropertyTypeDto::fromArray([
                            'key' => $field['field'],
                            'group' => $i,
                            'config' => $innerPropertyType,
                        ]),
                    ])->setFallbackLocale($this->getFallbackLocale());

                    $propertyTypes[] = $innerPropertyType;

                }

                return PropertyDataGroupDto::fromArray([
                    'key' => $i,
                    'data' => $result,
                    'propertyTypes' => $propertyTypes,
                ])->setFallbackLocale($this->getFallbackLocale());

            })->toArray();

        } elseif ($propertyType->isTranslatable()) {

            $value = TranslatableHelper::getTranslations(
                $sourceValue,
                $locale ?? $this->getFallbackLocale(),
                $this->getFallbackLocale()
            );

            return $this->transformPropertyValueWithoutTranslatable($value, $propertyType, $locale);
        } else {
            return $this->transformPropertyValueWithoutTranslatable($sourceValue, $propertyType, $locale);
        }
    }

    protected function transformPropertyValueWithoutTranslatable($sourceValue, FieldTypeConfig $propertyType, ?string $locale)
    {
        $locale ??= $this->getFallbackLocale();
        switch (true) {
            case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor:
            case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\RichEditor:
                return new HtmlString($sourceValue);

            case $propertyType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\DateTimePicker:

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

            case $propertyType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image:
                $disk = $propertyType->disk ?? config('filesystems.default');
                $directory = $propertyType->directory;

                return collect($sourceValue)
                    ->map(fn ($path) => FileDto::fromArray([
                        'path' => $path,
                        'disk' => $disk,
                        'directory' => $directory,
                    ]))->values()->all();

            case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\MediaPicker:

                // todo: improve performance
                $mediaAssets = inspirecms_asset()->findByKeys($sourceValue);

                // sort the content based on the source value
                return collect($sourceValue)
                    ->map(fn ($id) => $mediaAssets->first(fn ($v) => $v->getKey() == $id))
                    ->whereInstanceOf(\SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset::class)
                    ->map(fn ($m) => $m->toDto($locale))
                    ->values();

            case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\ContentPicker:

                // todo: improve performance
                $content = inspirecms_content()->findPublishedContentByIds($sourceValue)
                    ->filter(fn ($c) => in_array($c->getKey(), $sourceValue))
                    ->sortBy(fn ($c) => array_search($c->getKey(), $sourceValue))
                    ->values();

                if ($content instanceof \SolutionForest\InspireCms\Collection\ContentCollection) {
                    $content = $content->toDto($locale);
                } else {
                    $content = new \SolutionForest\InspireCms\Collection\ContentCollection($content->map(fn ($c) => $c->toDto($locale))->values());
                }

                return $content;

            default:
                return $sourceValue;
        }
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

            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor,
            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\RichEditor => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p><p>Curabitur non nulla sit amet nisl <b>tempus</b> convallis quis ac lectus.</p>',

            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\Tags => ['tag1', 'tag2'],

            default => null,
        };
    }
}
