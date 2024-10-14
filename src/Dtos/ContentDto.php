<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Collection;
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
     * @var Collection<PropertyDataGroupDto>
     */
    public $propertyData;

    protected array $translatableAttributes = ['title'];

    public static function fromTranslatableModel($model, $locale)
    {
        $model->loadMissing(['documentType']);
        /**
         * @var self
         */
        $dto = parent::fromTranslatableModel($model, $locale);

        if ($model->documentType) {
            $dto->documentType = DocumentTypeDto::fromModel($model->documentType);
        }
        $dto->setPropertyData($model->getLatestPublishedPropertyData());

        return $dto;
    }

    /**
     * @return self
     */
    public function setPropertyData(array $propertyData)
    {
        $this->propertyData = collect($propertyData)->map(function ($arr, $group) {

            $data = collect($arr)->map(
                fn ($value, $key) => PropertyDataDto::fromArray([
                    'propertyKey' => $key,
                    'propertyValue' => $value,
                    'config' => $this->documentType?->getField($key)?->config,
                ])
            )
                ->values();

            return PropertyDataGroupDto::fromArray([
                'name' => $group,
                'data' => $data,
            ])->setFallbackLocale($this->getFallbackLocale());

        })->values();

        return $this;
    }

    /**
     * @param  mixed  $locale
     * @return Collection<PropertyDataDto>
     */
    public function getPropertyData(string $name, ?string $locale = null)
    {
        $groups = $this->propertyData->filter(fn (PropertyDataGroupDto $propertyData) => $propertyData->data?->contains('propertyKey', $name));
        $result = collect();

        foreach ($groups as $group) {
            $result = $result->put($group->name, $group->getPropertyData($name, $locale));
        }

        return $result;
    }

    /**
     * @return ?PropertyDataGroupDto
     */
    public function getPropertyGroup(string $name)
    {
        return $this->propertyData->first(fn (PropertyDataGroupDto $propertyData) => $propertyData->name === $name);
    }

    /**
     * @return null|string|array<string,string>
     */
    public function getTitle(?string $locale = null)
    {
        return $this->getTranslation('title', $locale);
    }
}
