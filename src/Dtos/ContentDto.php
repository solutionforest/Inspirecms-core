<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection as SupportCollection;
use SolutionForest\InspireCms\Helpers\SeoHelper;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableDto;

class ContentDto extends BaseTranslatableDto
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
     * @var array<string,string>
     */
    public $urls;

    /**
     * @var DocumentTypeDto
     */
    public $documentType;

    /**
     * @var SupportCollection<PropertyDataGroupDto>
     */
    public $propertyData;

    /**
     * @var SupportCollection<string,SeoDto>
     */
    public $seo;

    /**
     * @var SupportCollection<CotnentDto>
     */
    public $children;

    protected array $translatableAttributes = ['title'];

    public static function fromTranslatableArray(array $parameters, $locale, $fallbackLocale, $availableLocales = [])
    {
        return parent::fromTranslatableArray(
            static::mutuateParameters($parameters, [$locale, $fallbackLocale, $availableLocales]), 
            $locale, 
            $fallbackLocale, 
            $availableLocales
        );
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
        return $this->children ?? collect();
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
        $urls = collect($this->urls);
        return $urls->get($locale ?? $this->getLocale()) ?? $urls->get($this->getFallbackLocale());
    }

    public function getSeo(?string $locale = null)
    {
        return $this->seo->get($locale ?? $this->getLocale()) ?? $this->seo->get($this->getFallbackLocale());
    }

    //region Helpers
    protected static function mutuateParameters(array $parameters, array $configs): array
    {
        [$locale, $fallbackLocale, $availableLocales] = $configs;

        $parameters['propertyData'] = static::mutuatePropertyData($parameters['propertyData'] ?? [], $parameters['documentType'] ?? null, $fallbackLocale);
        $parameters['seo'] = static::mutuateSeoData($parameters['seo'] ?? []);

        return $parameters;
    }

    /**
     * @param ?DocumentTypeDto $documentTypes
     * @return array
     */
    protected static function mutuatePropertyData(array $propertyData, $documentType, $fallbackLocale)
    {
        return collect($propertyData)->map(function ($arr, $groupName) use ($documentType, $fallbackLocale): PropertyDataGroupDto {

            $data = collect($arr)->map(
                fn ($value, $key) => PropertyDataDto::fromArray([
                    'propertyKey' => $key,
                    'propertyValue' => $value,
                    'config' => $documentType?->getField($key)?->config,
                ])
            )
                ->values();

            return PropertyDataGroupDto::fromArray([
                'name' => $groupName,
                'data' => $data,
            ])->setFallbackLocale($fallbackLocale);

        })->values();
    }

    protected static function mutuateSeoData(array $seoData)
    {
        $result = [];

        foreach ($seoData as $locale => $data) {

            if ($data instanceof SeoDto) {
                $result[$locale] = $data;
                continue;
            } elseif (!is_array($data)) {
                continue;
            }
            
            foreach ($data as $key => $value) {

                if (in_array($key, SeoHelper::getTranslatableAttributes()) && is_array($value)) {
                    $value = data_get($value, $locale, null);
                }

                $data[$key] = $value;
            }
            
            $result[$locale] = SeoDto::fromArray($data);
        }

        return collect($result);
    }
    //endregion Helpers
}
