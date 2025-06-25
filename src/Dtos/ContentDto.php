<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use SolutionForest\InspireCms\Base\Dtos\Concerns\HasPropertyGroup;
use SolutionForest\InspireCms\Collection\ContentCollection;
use SolutionForest\InspireCms\Dtos\Collection\PropertyGroupCollection;
use SolutionForest\InspireCms\Helpers\ContentHelper;
use SolutionForest\InspireCms\Helpers\SeoHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableModelDto;

/**
 * @extends BaseTranslatableModelDto<Content|Model,ContentDto>
 */
class ContentDto extends BaseTranslatableModelDto
{
    use HasPropertyGroup;

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
     * @var ?string
     */
    public $documentType;

    /**
     * @var null|SupportCollection<ContentDto>
     */
    protected $children = null;

    /**
     * @var null|SupportCollection<ContentDto>
     */
    protected $ancestors = null;

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
     * @return SupportCollection<ContentDto>
     */
    public function getChildren()
    {
        if ($this->children != null) {
            return $this->children;
        }

        $model = $this->getModel();

        $children = $model?->children ?? collect();
        $children->map->loadMissing(static::getNecessaryRelationships());

        $currLocale = $this->getLocale();
        $result = $children instanceof ContentCollection
            ? $children->toDto($currLocale)
            : ContentCollection::make($children)->toDto($currLocale);

        return $this->children = $result;
    }

    public function getPaginatedChildren($perPage = 10, $pageName = 'page', $page = null)
    {
        // todo: implement pagination for children
        return $this->getChildren()->paginate($perPage, $pageName, $page);
    }

    public function getParent()
    {
        return $this->getAncestors()->first();
    }

    public function getAncestors()
    {
        if ($this->ancestors != null) {
            return $this->ancestors;
        }

        $model = $this->getModel();

        $ancestors = $model->ancestors->reverse() ?? collect();
        $ancestors->map->loadMissing(static::getNecessaryRelationships());

        $currLocale = $this->getLocale();
        $result = $ancestors instanceof ContentCollection
            ? $ancestors->toDto($currLocale)
            : ContentCollection::make($ancestors)->toDto($currLocale);

        return $this->ancestors = $result;
    }

    public function getTemplate($slug)
    {
        // todo: improve this
        $content = $this->getModel();
        $template = inspirecms_content()->getTemplateFor($content, $slug);

        return $template?->toDto() ?? null;
    }

    /**
     * Checks if a specific property exists for the given group and field.
     *
     * @param  string  $group  The property group to check
     * @param  string  $field  The specific field name to check within the group
     * @return bool Returns true if the property exists, false otherwise
     */
    public function hasProperty(string $group, string $field): bool
    {
        return $this->getPropertyGroup($group)?->hasProperty($field) ?? false;
    }

    /**
     * Gets the value of a specific property within a group.
     *
     * @param  string  $group  The property group identifier
     * @param  string  $field  The field identifier within the group
     * @param  string|null  $locale  Optional locale code to get localized value, defaults to current locale if null
     * @return mixed
     */
    public function getPropertyValue(string $group, string $field, ?string $locale = null, ?string $fallbackLocale = null)
    {
        $locale ??= $this->getLocale() ?? $this->getFallbackLocale();
        $fallbackLocale ??= $this->getFallbackLocale();

        return $this->getPropertyGroup($group)?->getPropertyData($field, $fallbackLocale)?->getValue($locale) ?? null;
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

        $locale ??= $this->getLocale();

        $result = $seo->get($locale);

        // Using fallback locale
        if (! $result && 
            ($fallbackLocale = $this->getFallbackLocale()) && 
            ($fallbackSeo = $seo->get($fallbackLocale))
        ) {
            $result = $fallbackSeo;
        }

        // Using default SEO data if not found
        if (! $result) {
            $result = inspirecms()->getFallbackSeo();
        }

        return $result;
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

        $dtoParameters['seo'] = collect($availableLanguages)->keys()->mapWithKeys(function ($locale) use ($record) {
            $seo = $record->webSetting?->toDto($locale);

            if (
                $seo instanceof SeoDto
                && ($ancestors = collect($record->ancestorsAndSelf)->where(fn ($item) => $item->getKey() !== $record->getKey())->pluck('webSetting')->map(fn ($item) => $item?->toDto($locale)))
            ) {
                $seo->setAncestors($ancestors);
            }

            return [
                $locale => $seo,
            ];
        })->all();

        $dtoParameters['urls'] = collect($availableLanguages)->mapWithKeys(fn (LanguageDto $lang) => [
            $lang->code => $record->getUrl($lang),
        ])->all();

        $dtoParameters['propertyTypes'] = collect($record?->documentType?->fields)->map(fn ($field) => $field->toDto());
        $dtoParameters['type'] = $record?->documentType?->category;
        $dtoParameters['documentType'] = $record?->documentType?->slug;

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
        return ContentHelper::getDtoRequiredRelations();
    }
    // endregion Helpers
}
