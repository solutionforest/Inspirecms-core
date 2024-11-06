<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Dtos\Assets\FileDto;
use SolutionForest\InspireCms\Dtos\Assets\MediaAssetDto;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\Translatable;

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
                    ->map(fn ($path) => FileDto::fromArray([
                        'path' => $path,
                        'disk' => $disk,
                        'directory' => $directory,
                    ]))->values()->all();

            case $propertyType instanceof \SolutionForest\InspireCms\FieldTypes\Configs\MediaPicker:

                return MediaAssetDto::fromArray([
                    'keys' => $propertyDataValue,
                ]);

            default:
                return $propertyDataValue;
        }
    }
}
