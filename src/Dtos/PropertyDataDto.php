<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
use SolutionForest\InspireCms\Fields\Dtos\FileDto;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Helpers\TranslatableHelper;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

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
            return $this->processPropertyType($propertyDataValue, $propertyType, $locale);
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

    protected function processPropertyType($sourceValue, ?FieldTypeConfig $propertyType, ?string $locale)
    {
        switch (true) {

            case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\Translate:

                $innerPropertyType = FilamentFieldGroup::getFieldTypeConfig($propertyType->field);

                // If the inner property type is not found, return null.
                if (is_null($innerPropertyType)) {
                    return null;
                }

                $value = TranslatableHelper::getTranslations(
                    $sourceValue,
                    $locale,
                    $this->fallbackLocale
                );

                return $this->processPropertyType($value, $innerPropertyType, $locale);

            case $propertyType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image:
                $disk = $propertyType->disk ?? config('filesystems.default');
                $directory = $propertyType->directory;

                return collect($sourceValue)
                    ->map(fn ($path) => FileDto::fromArray([
                        'path' => $path,
                        'disk' => $disk,
                        'directory' => $directory,
                    ]))->values()->all();

            // case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\MediaPicker:

            //     $mediaAssets = inspirecms_asset()->findByKeys($sourceValue);

            //     return collect($mediaAssets)
            //         ->whereInstanceOf(\SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset::class)
            //         ->filter()
            //         ->map(fn ($m) => $m->toDto())
            //         ->values()
            //         ->all();

            // case $propertyType instanceof \SolutionForest\InspireCms\Fields\Configs\ContentPicker:

            //     //todo: improve performance
            //     $content = InspireCmsConfig::getContentModelClass()::whereIsPublished()->findMany($sourceValue);

            //     // sort the content based on the source value
            //     return collect($sourceValue)
            //         ->map(fn ($id) => $content->first(fn ($c) => $c->getKey() == $id)?->toDto($locale))
            //         ->values()
            //         ->all();

            default:
                return $sourceValue;
        }
    }
}
