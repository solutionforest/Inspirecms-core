<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Collection;
use SolutionForest\InspireCms\FieldTypes\Configs\Translate;
use SolutionForest\InspireCms\Models\Content;

/**
 * @extends BaseTranslatableModelDto<Content>
 */
class ContentDto extends BaseTranslatableModelDto
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var array<string,string>
     */
    public $title;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var DocumentTypeDto
     */
    public $documentType;

    /**
     * @var Collection<PropertyDataDto>
     */
    public $propertyData;

    protected array $translatableAttributes = ['title'];

    public static function fromTranslatableModel($model, $locale)
    {
        /**
         * @var self
         */
        $dto = parent::fromTranslatableModel($model, $locale);

        $dto->setPropertyData($model->getLatestPublishedPropertyData());

        return $dto;
    }

    /**
     * @return self
     */
    public function setPropertyData(array $propertyData)
    {
        $this->propertyData = collect($propertyData)->map(function ($value, $key) {

            return [
                'propertyKey' => $key,
                'propertyValue' => $value,
            ];

        })->values()->map(fn ($data) => PropertyDataDto::fromArray($data));

        return $this;
    }

    public function getPropertyData(string $name, ?string $locale = null)
    {
        $propertyType = $this->documentType?->getField($name)?->config;
        $propertyData = $this->propertyData->first(fn ($propertyData) => $propertyData->propertyKey === $name)?->propertyValue;

        switch (true) {
            case $propertyType instanceof Translate:
                return $this->getTranslations($propertyData, $locale);
            case $propertyType instanceof \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image:
                $disk = $propertyType->disk ?? config('filesystems.default');
                $directory = $propertyType->directory;

                return collect($propertyData)
                    ->map(fn ($file) => filled($directory) ? $directory . '/' . $file : $file)
                    ->map(fn ($filePath) => [
                        'path' => $filePath,
                        'disk' => $disk,
                    ])
                    ->values()->all();
            default:
                return $propertyData;
        }
    }

    /**
     * @return null|string|array<string,string>
     */
    public function getTitle(?string $locale = null)
    {
        return $this->getTranslation('title', $locale);
    }
}
