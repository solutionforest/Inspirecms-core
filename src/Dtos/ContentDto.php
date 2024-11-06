<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
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
     * @var SupportCollection<PropertyDataGroupDto>
     */
    public $propertyData;

    /**
     * @var SeoDto
     */
    protected array $translatableAttributes = ['title'];

    public static function fromTranslatableModel($model, $locale, bool $withChildren = true): self
    {
        $model->loadMissing([
            'documentType.fieldGroups.fields',
            'webSetting',
            ...($withChildren ? ['children'] : []),
        ]);
        /**
         * @var self
         */
        $dto = parent::fromTranslatableModel($model, $locale);
        $dto->setSeoData($model);

        if ($model->documentType) {
            $dto->documentType = DocumentTypeDto::fromModel($model->documentType);
        }
        $dto->setPropertyData($model->getLatestPublishedPropertyData());

        // avoid loading children if not needed
        $dto->children = $model->children->map(fn ($child) => self::fromTranslatableModel($child, $locale, false));

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
     * @return ?PropertyDataGroupDto
     */
    public function getPropertyGroup(string $name)
    {
        return $this->propertyData->first(fn (PropertyDataGroupDto $propertyData) => $propertyData->name === $name);
    }

    /**
     * @return SupportCollection<ContentDto>
     */
    public function getChildren()
    {
        ray($this);
        if (isset($this->children)) {
            return $this->children ?? collect();
        }

        return $this->children = $this->getModel()->children->map(fn ($child) => self::fromTranslatableModel($child, $this->getLocale(), false));
    }

    /**
     * @param  mixed  $locale
     * @return SupportCollection<PropertyDataDto>
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

    public function setSeoData(Model | array $model)
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
        } elseif (is_array($model)) {
            $dataBefore = $model;
        }

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

    /**
     * @return null|string|array<string,string>
     */
    public function getTitle(?string $locale = null)
    {
        return $this->getTranslation('title', $locale);
    }

    /**
     * @return null|string|array<string,string>
     */
    public function getUrl(?string $locale = null)
    {
        return $this->getModel()?->getUrl($locale);
    }
}
