<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use SolutionForest\InspireCms\Helpers\SeoHelper;
use SolutionForest\InspireCms\Models\Content;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableModelDto;

/**
 * @extends BaseTranslatableModelDto<Content,ContentDto>
 */
class ContentDto extends BaseTranslatableModelDto
{
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
     * @var SupportCollection<PropertyTypeDto>
     */
    public $propertyTypes;

    /**
     * @var SupportCollection<PropertyDataGroupDto>
     */
    public $propertyData;

    /**
     * @var SupportCollection<string,SeoDto>
     */
    public $seo;

    /**
     * @var null|SupportCollection<ContentDto>|\SolutionForest\InspireCms\Collection\ContentDtoCollection
     */
    public $children = null;

    /**
     * @var ?array<string,string>
     */
    public $redirectUrls;

    /**
     * @var ?int
     */
    public $redirectType;

    protected array $translatableAttributes = ['title'];

    /**
     * @param  Content  $model
     * @param  string  $locale
     */
    public static function make($model, array $propertyData, $locale)
    {
        $availableLanguages = inspirecms()->getAllAvailableLanguages();

        $availableLocales = collect($availableLanguages)->map(fn ($lang) => $lang->code)->toArray();

        $parameters = static::prepareDtoParameters($model, $propertyData, $availableLanguages);

        $fallbackLocale = $model->getFallbackLocale() ?? 'en';

        $dto = parent::fromArray($parameters);

        $dto->setModel($model);
        $dto->setLocale($locale);
        $dto->setFallbackLocale($fallbackLocale);
        $dto->setAvailableLocales($availableLocales);

        return $dto;
    }

    /**
     * @return SupportCollection<ContentDto>|\SolutionForest\InspireCms\Collection\ContentDtoCollection
     */
    public function getChildren()
    {
        if ($this->children != null) {
            return $this->children;
        }

        $model = $this->getModel();
        if (is_null($model)) {
            $children = collect();
        }

        if (! $model?->relationLoaded('children')) {
            $children = $model->children()->with(static::getNecessaryRelationships())->get() ?? collect();
        } else {
            $children = $model->children ?? collect();
        }
        $result = new \SolutionForest\InspireCms\Collection\ContentDtoCollection;
        foreach ($children as $child) {
            $propertyData = $child->getLatestPublishedPropertyData();
            $result->push(static::make($child, $propertyData, $this->getLocale()));
        }

        return $this->children = $result;
    }

    /**
     * Retrieves the property group associated with the given key.
     *
     * @param  string  $key  The key identifying the property group.
     * @return ?PropertyDataGroupDto
     */
    public function getPropertyGroup(string $key)
    {
        return collect($this->propertyData)->first(fn (PropertyDataGroupDto $p) => $p->key === $key);
    }

    /**
     * Retrieve the property data associated with a specific property key.
     *
     * @param  string  $key  The key of the property to retrieve data for.
     * @return SupportCollection<string,PropertyDataDto>
     */
    public function getPropertyData(string $key)
    {
        $result = collect();

        foreach ($this->propertyData ?? [] as $group) {

            if (! $group instanceof PropertyDataGroupDto) {
                continue;
            }

            // Determine the property group contains the property
            $propertyData = collect($group->data)->first(fn (PropertyDataDto $d) => $d->key === $key);

            if (! $propertyData) {
                continue;
            }

            $result = $result->put($group->key, $propertyData);
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
        $seo = collect($this->seo);

        return $seo->get($locale ?? $this->getLocale()) ?? $seo->get($this->getFallbackLocale());
    }

    public function isRedirectable(): bool
    {
        return ! is_null($this->redirectUrls);
    }

    public function getRedirectUrl(?string $locale = null): ?string
    {
        $urls = collect($this->redirectUrls);

        return $urls->get($locale ?? $this->getLocale()) ?? $urls->get($this->getFallbackLocale());
    }
    //region Helpers

    protected static function prepareDtoParameters(Model $record, array $propertyData, array $availableLanguages): array
    {
        $record->loadMissing(static::getNecessaryRelationships());

        $dtoParameters = $record->toArray();

        $dtoParameters['seo'] = collect($availableLanguages)->keys()->mapWithKeys(fn ($locale) => [
            $locale => $record->webSetting?->toDto($locale),
        ])->all();

        $dtoParameters['urls'] = collect($availableLanguages)->mapWithKeys(fn (LanguageDto $lang) => [
            $lang->code => $record->getUrl($lang),
        ])->all();

        $dtoParameters['propertyTypes'] = collect($record?->documentType?->fields)->map(fn ($field) => $field->toDto());

        $dtoParameters['propertyData'] = $propertyData;

        if ($record->isRedirectable()) {
            $dtoParameters['redirectUrls'] = collect($availableLanguages)->mapWithKeys(fn (LanguageDto $lang) => [
                $lang->code => $record->getRedirectUrl($lang),
            ])->all();
            $dtoParameters['redirectType'] = $record->getRedirectType();
        }

        unset($dtoParameters['children']);  // Get by model

        return static::mutuateParameters($dtoParameters, [$record->getFallbackLocale(), $availableLanguages]);
    }

    protected static function mutuateParameters(array $parameters, array $configs): array
    {
        [$fallbackLocale, $availableLanguages] = $configs;

        $propertyTypes = collect($parameters['propertyTypes'] ?? [])
            ->map(fn ($propertyType) => is_array($propertyType) ? PropertyTypeDto::fromArray($propertyType) : $propertyType)
            ->where(fn ($propertyType) => $propertyType instanceof PropertyTypeDto)
            ->values()
            ->toArray();

        $parameters['propertyData'] = static::mutuatePropertyData($parameters['propertyData'] ?? [], $propertyTypes, $fallbackLocale);
        $parameters['seo'] = static::mutuateSeoData($parameters['seo'] ?? []);

        // Ensure is a collection
        $parameters['propertyTypes'] = collect($propertyTypes);
        $parameters['propertyData'] = collect($parameters['propertyData'] ?? []);
        $parameters['seo'] = collect($parameters['seo'] ?? []);

        return $parameters;
    }

    /**
     * Mutates the property data based on the provided property types and optional fallback locale.
     *
     * @param  array  $propertyData  The property data to be mutated.
     * @param  array  $propertyTypes  The types of properties to be used for mutation.
     * @param  string|null  $fallbackLocale  The optional fallback locale to be used if necessary.
     * @return array
     */
    protected static function mutuatePropertyData(array $propertyData, array $propertyTypes, $fallbackLocale = null)
    {
        $result = [];

        foreach ($propertyData as $groupName => $item) {

            $propertyDataItems = [];

            $groupPropertyTypes = collect($propertyTypes)->where(fn (PropertyTypeDto $p) => $p->group === $groupName)->values();

            foreach ($item as $key => $value) {

                $propertyType = collect($groupPropertyTypes)->first(fn (PropertyTypeDto $p) => $p->key === $key);

                /** @var PropertyDataDto */
                $propertyDataItem = PropertyDataDto::fromArray([
                    'key' => $key,
                    'value' => $value,
                    'propertyType' => $propertyType,
                ]);

                $propertyDataItem->setFallbackLocale($fallbackLocale);

                $propertyDataItems[] = $propertyDataItem;
            }

            $result[] = PropertyDataGroupDto::fromArray([
                'key' => $groupName,
                'data' => $propertyDataItems,
                'propertyTypes' => $groupPropertyTypes,
            ]);
        }

        return $result;
    }

    /**
     * Mutates the given SEO data array.
     *
     * @param  array  $seoData  The SEO data to be mutated.
     * @return array
     */
    protected static function mutuateSeoData(array $seoData)
    {
        $result = [];

        foreach ($seoData as $locale => $data) {

            if ($data instanceof SeoDto) {
                $result[$locale] = $data;

                continue;
            } elseif (! is_array($data)) {
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

        return $result;
    }

    protected static function getNecessaryRelationships(): array
    {
        return [
            'webSetting',
            'publishedVersions',
            'documentType.fields.group',
        ];
    }
    //endregion Helpers
}
