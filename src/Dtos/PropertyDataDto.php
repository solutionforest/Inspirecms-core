<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\HtmlString;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Fields\Dtos\FileDto;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Helpers\TranslatableHelper;

/**
 * @extends BaseDto<PropertyDataDto>
 */
class PropertyDataDto extends BaseDto
{
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

    protected ?string $fallbackLocale = null;

    public function getValue(?string $locale = null): mixed
    {
        $propertyType = $this->propertyType?->config;

        $propertyDataValue = $this->getSourceValue();

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

    public function setFallbackLocale(?string $locale): self
    {
        $this->fallbackLocale = $locale;

        return $this;
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
                        return null;
                    }

                    $innerPropertyType = FieldTypeHelper::getFieldTypeConfig($field['field'], $field['fieldConfig'] ?? []);

                    if (is_null($innerPropertyType)) {
                        return null;
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
                    ])->setFallbackLocale($this->fallbackLocale);

                    $propertyTypes[] = $innerPropertyType;

                }

                return PropertyDataGroupDto::fromArray([
                    'key' => $i,
                    'data' => $result,
                    'propertyTypes' => $propertyTypes,
                ]);

            })->toArray();

        } elseif ($propertyType->isTranslatable()) {

            $value = TranslatableHelper::getTranslations(
                $sourceValue,
                $locale,
                $this->fallbackLocale
            );

            return $this->transformPropertyValueWithoutTranslatable($value, $propertyType, $locale);
        } else {
            return $this->transformPropertyValueWithoutTranslatable($sourceValue, $propertyType, $locale);
        }
    }

    protected function transformPropertyValueWithoutTranslatable($sourceValue, FieldTypeConfig $propertyType, ?string $locale)
    {
        switch (true) {
            case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor:
            case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\RichEditor:
                return new HtmlString($sourceValue);

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

                //todo: improve performance
                $mediaAssets = inspirecms_asset()->findByKeys($sourceValue);

                // sort the content based on the source value
                return collect($sourceValue)
                    ->map(fn ($id) => $mediaAssets->first(fn ($v) => $v->getKey() == $id))
                    ->whereInstanceOf(\SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset::class)
                    ->map(fn ($m) => $m->toDto($locale))
                    ->values()
                    ->all();

            case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\ContentPicker:

                //todo: improve performance
                $content = inspirecms_content()->findContentByIds($sourceValue);

                // sort the content based on the source value
                return collect($sourceValue)
                    ->map(fn ($id) => $content->first(fn ($c) => $c->getKey() == $id))
                    ->whereInstanceOf(\SolutionForest\InspireCms\Models\Contracts\Content::class)
                    ->map(fn ($c) => $c->toDto($locale))
                    ->values()
                    ->all();

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
            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor,
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Textarea => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\RichEditor => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p><p>Curabitur non nulla sit amet nisl <b>tempus</b> convallis quis ac lectus.</p>',
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Url => 'https://example.com',
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Text => 'Lorem ipsum dolor sit amet',
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Number => 123,
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Boolean => true,
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Date => now()->format('Y-m-d'),
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\DateTime => now()->format('Y-m-d H:i:s'),
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Time => now()->format('H:i:s'),
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Select => $fieldType->options[0]['value'] ?? null,
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Radio => $fieldType->options[0]['value'] ?? null,
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Checkbox => $fieldType->options[0]['value'] ?? null,
            $fieldType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\ColorPicker => '#000000',
            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\MediaPicker => [KeyHelper::generateMinUuid()],
            $fieldType instanceof \SolutionForest\InspireCms\Fields\Configs\ContentPicker => [],
            default => null,
        };
    }
}
