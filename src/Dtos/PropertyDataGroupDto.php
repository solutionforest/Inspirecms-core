<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Dtos\Concerns\Translatable;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class PropertyDataGroupDto extends BaseDto
{
    use Translatable;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Collection<PropertyDataDto>
     */
    public $data;

    /**
     * @return self
     */
    public static function fromArray(array $parameters)
    {
        $dto = parent::fromArray($parameters);
        $dto->data ??= collect();

        return $dto;
    }

    public function getPropertyData(string $name, ?string $locale = null)
    {
        $propertyData = $this->data->first(fn ($propertyData) => $propertyData->propertyKey === $name);
        $propertyDataValue = $propertyData?->propertyValue;
        $propertyType = $propertyData?->config;

        switch (true) {
            case $propertyType instanceof \SolutionForest\InspireCms\FieldTypes\Configs\Translate:
                return $this->getTranslations($propertyDataValue, $locale ?? $this->getLocale());

            case $propertyType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image:
                $disk = $propertyType->disk ?? config('filesystems.default');
                $directory = $propertyType->directory;

                return collect($propertyDataValue)
                    ->map(fn ($file) => filled($directory) ? $directory . '/' . $file : $file)
                    ->map(fn ($filePath) => [
                        'path' => $filePath,
                        'disk' => $disk,
                    ])
                    ->values()->all();

            case $propertyType instanceof \SolutionForest\InspireCms\FieldTypes\Configs\MediaPicker:
                $media = InspireCmsConfig::getMediaAssetModelClass()::with('media')->findMany($propertyDataValue)->map(fn ($mediaAsset) => $mediaAsset->media)->flatten();

                return collect($media)
                    ->map(fn ($media) => [
                        'path' => $media->file_name,
                        'disk' => $media->disk,
                        'url' => $media->getUrl(),
                    ])
                    ->values()->all();

            default:
                return $propertyDataValue;
        }
    }
}
