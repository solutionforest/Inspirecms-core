<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use SolutionForest\InspireCms\Dtos\Collection\PropertyGroupCollection;
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
     * @var ?string
     */
    public $type;

    /**
     * @var ?\Carbon\CarbonInterface
     */
    public $publishAt;

    /**
     * @var array<string,string>
     */
    public $urls;

    /**
     * @var SupportCollection<PropertyTypeDto>
     */
    public $propertyTypes;

    /**
     * @var PropertyGroupCollection
     */
    public $propertyData;

    /**
     * @var SupportCollection<string,SeoDto>
     */
    public $seo;

    /**
     * @var ?array<string,string>
     */
    public $redirectUrls;

    /**
     * @var ?int
     */
    public $redirectType;

    /**
     * @var null|SupportCollection<ContentDto>
     */
    protected $children = null;

    protected array $translatableAttributes = ['title'];

    /**
     * @param  Content  $model
     * @param  string  $locale
     */
    public static function make($model, array $propertyData, $locale, ?\Carbon\CarbonInterface $publishAt = null)
    {
        $availableLanguages = inspirecms()->getAllAvailableLanguages();

        $availableLocales = collect($availableLanguages)->map(fn ($lang) => $lang->code)->toArray();

        $parameters = static::prepareDtoParameters($model, $propertyData, $availableLanguages);
        $parameters['publishAt'] = $publishAt;

        $fallbackLocale = $model->getFallbackLocale() ?? 'en';

        $dto = parent::fromArray($parameters);

        $dto->setModel($model);
        $dto->setLocale($locale);
        $dto->setFallbackLocale($fallbackLocale);
        $dto->setAvailableLocales($availableLocales);

        return $dto;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model&\SolutionForest\InspireCms\Models\Contracts\DocumentType  $documentType
     * @return \SolutionForest\InspireCms\Dtos\ContentDto
     */
    public static function fakeForDocumentType($documentType, array $propertyData = [], ?\Carbon\CarbonInterface $publishAt = null)
    {
        $availableLanguages = inspirecms()->getAllAvailableLanguages();

        $availableLocales = collect($availableLanguages)->map(fn ($lang) => $lang->code)->toArray();

        $fallbackLocale = array_key_first($availableLocales) ?? 'en';

        /** @var SupportCollection<PropertyTypeDto> */
        $propertyTypes = collect($documentType?->fields)->map(fn ($field) => $field->toDto());
        $dtoParameters['propertyTypes'] = $propertyTypes;

        foreach ($propertyTypes as $propertyType) {
            if (! $propertyType instanceof PropertyTypeDto) {
                continue;
            }
            $propertyData[$propertyType->group][$propertyType->key] = PropertyDataDto::fakeValueForPropertyType($propertyType, array_keys($availableLocales));
        }
        $dtoParameters['propertyData'] = $propertyData;

        $parameters = static::mutuateParameters($dtoParameters, [$fallbackLocale, $availableLanguages]);

        $parameters['publishAt'] = $publishAt;
        $dto = parent::fromArray($parameters);

        $dto->setLocale($fallbackLocale);
        $dto->setFallbackLocale($fallbackLocale);
        $dto->setAvailableLocales($availableLocales);

        return $dto;
    }

    /**
     * @return SupportCollection<ContentDto>
     */
    public function getChildren()
    {
        if ($this->children != null) {
            return $this->children;
        }

        $model = $this->getModel();

        if (is_null($model)) {
            $children = collect();
        } elseif (! $model->relationLoaded('children')) {
            $children = $model->children()->with(static::getNecessaryRelationships())->get() ?? collect();
        } else {
            $children = $model->children ?? collect();
        }

        $currLocale = $this->getLocale();
        $result = $children instanceof \SolutionForest\InspireCms\Collection\ContentCollection
            ? $children->toDto($currLocale)
            : (new \SolutionForest\InspireCms\Collection\ContentCollection($children))->toDto($currLocale);

        return $this->children = $result;
    }

    public function getTemplate($slug)
    {
        // todo: improve this
        $content = $this->getModel();
        $template = inspirecms_content()->getTemplateFor($content, $slug);

        return $template?->toDto() ?? null;
    }

    /**
     * Retrieves the property group associated with the given key.
     *
     * @param  string  $key  The key identifying the property group.
     * @return ?PropertyDataGroupDto
     */
    public function getPropertyGroup(string $key)
    {
        if (! $this->propertyData instanceof PropertyGroupCollection) {
            $this->propertyData = new PropertyGroupCollection($this->propertyData);
        }

        $target = $this->propertyData->get($key);
        if ($target && ($locale = $this->getLocale() ?? $this->getFallbackLocale()) != null) {
            $target->setFallbackLocale($locale);
        }

        return $target;
    }

    /**
     * Retrieve the property data associated with a specific property key.
     *
     * @param  string  $key  The key of the property to retrieve data for.
     * @return SupportCollection<string,PropertyDataDto>
     */
    public function getPropertyData(string $key)
    {
        if (! $this->propertyData instanceof PropertyGroupCollection) {
            $this->propertyData = new PropertyGroupCollection($this->propertyData);
        }

        $groups = clone $this->propertyData;
        if (($locale = $this->getLocale() ?? $this->getFallbackLocale()) != null) {
            $groups->setFallbackLocale($locale);
        }

        return $groups->getPropertyData($key);
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

    // region Helpers
    /**
     * @param  Model | \SolutionForest\InspireCms\Models\Contracts\Content  $record
     */
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
        $dtoParameters['type'] = $record?->documentType?->category;

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
            ->whereInstanceOf(PropertyTypeDto::class)
            ->values()
            ->toArray();

        $parameters['seo'] = static::mutuateSeoData($parameters['seo'] ?? []);

        // Ensure is a collection
        $parameters['propertyTypes'] = collect($propertyTypes);
        $parameters['propertyData'] = new PropertyGroupCollection(
            static::mutuatePropertyData($parameters['propertyData'] ?? [], $propertyTypes, $fallbackLocale)
        );
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

            $groupPropertyTypes = collect($propertyTypes)
                ->where(fn (PropertyTypeDto $p) => $p->group === $groupName)
                ->values()
                ->mapWithKeys(fn (PropertyTypeDto $p) => [$p->key => $p]);

            foreach ($item as $key => $value) {

                $propertyType = $groupPropertyTypes->get($key);

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
    // endregion Helpers
}
