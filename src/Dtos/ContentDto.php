<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\SeoHelper;
use SolutionForest\InspireCms\Models\Content;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableModelDto;

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

    /**
     * @var SeoDto
     */
    public $seo;

    protected array $translatableAttributes = ['title'];

    public static function fromTranslatableModel($model, $locale)
    {
        $model->loadMissing(['documentType', 'webSetting']);
        /**
         * @var self
         */
        $dto = parent::fromTranslatableModel($model, $locale);
        $dto->setSeoData($model);

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

    public function setSeoData(Model $model)
    {
        if ($model instanceof Content) {

            $webSetting = $model->webSetting;
            $dataBefore = [
                ...($webSetting?->seo ?? []),
                ...($webSetting?->robots ?? []),
            ];

            $seoData['title'] = $this->getTranslations($dataBefore['meta_title'] ?? [], $this->getLocale()) ?? $this->getTitle();
            $seoData['locale'] = $this->getLocale();
            // todo: get image by id
            $seoData['image'] = $dataBefore['og_image'][0] ?? null;

            $mapper = [
                'meta_description' => 'description',
                'og_description' => 'ogDescription',
                'noindex' => 'noIndex',
                'nofollow' => 'noFollow',
                'noarchive' => 'noArchive',
                'nosnippet' => 'noSnippet',
                'noodp' => 'noOdp',
                'noydir' => 'noYdir',
            ];

            foreach ($mapper as $key => $value) {
                if (in_array($key, SeoHelper::getTranslatableAttributes())) {
                    $seoData[$value] = $this->getTranslations($dataBefore[$key] ?? [], $this->getLocale());
                } else {
                    $seoData[$value] = $dataBefore[$key] ?? false;
                }
            }

            $this->seo = SeoDto::fromArray($seoData);
        }
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
