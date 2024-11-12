<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Events;
use SolutionForest\InspireCms\Factories\ContentPathGeneratorFactory;
use SolutionForest\InspireCms\Factories\ContentUrlGeneratorFactory;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content as ContentContract;
use SolutionForest\InspireCms\Observers\ContentObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Models\Concerns\BelongsToNestableTree;
use SolutionForest\InspireCms\Support\Models\Concerns\HasAuthor;
use SolutionForest\InspireCms\Support\Models\Concerns\NestableTrait;

class Content extends BaseModel implements ContentContract
{
    use BelongsToNestableTree;
    use Concerns\HasContentVersions {
        prepareContentVersionData as protected traitPrepareContentVersionData;
    }
    use Concerns\HasContentWebSetting;
    use Concerns\HasTemplates;
    use Concerns\HasTranslations {
        setTranslation as protected traitSetTranslation;
        getTranslation as protected traitGetTranslation;
        getTranslations as protected traitGetTranslations;
    }
    use HasAuthor;
    use HasUuids;
    use NestableTrait {
        parent as protected traitParent;
    }
    use Searchable {
        queueMakeSearchable as protected traitQueueMakeSearchable;
        queueRemoveFromSearch as protected traitQueueRemoveFromSearch;
    }
    use SoftDeletes;

    protected $guarded = ['id'];

    public ?array $translatable = [
        'title',
        'propertyData',
    ];

    /**
     * @var array
     *
     * An array to temporarily store relation data for the Content model.
     * This array is used internally to manage and manipulate relationship data
     * before it is persisted to the database.
     */
    protected array $tempRelationData = [];

    protected $casts = [];

    protected $table = 'content';

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getDocumentTypeModelClass(), 'document_type_id');
    }

    public function sitemap(): MorphOne
    {
        return $this->morphOne(InspireCmsConfig::getSitemapModelClass(), 'model');
    }

    public function parent(): BelongsTo
    {
        // With nestable tree
        return $this->belongsTo(static::class, $this->getNestableParentIdName());
    }

    public function trashedParent(): BelongsTo
    {
        return $this->parent()->withTrashed();
    }

    public function navigation(): HasOne
    {
        return $this->hasOne(InspireCmsConfig::getNavigationModelClass(), 'content_id');
    }

    public function getFullSlug(?string $locale = null): string
    {
        return ContentPathGeneratorFactory::create()->getPath($this, $locale);
    }

    public function getUrl(?string $locale = null): string
    {
        return ContentUrlGeneratorFactory::create()->getUrl($this, $locale);
    }

    public function isPublished(?\Closure $callback = null): bool
    {
        $publishedAt = $this->getPublishTime();
        $status = $this->status;

        // If there's no publish date, it's not published
        if (is_null($publishedAt)) {
            return false;
        }

        $unpublishOption = inspirecms_content_statuses()->getOption('unpublish');
        if (is_null($unpublishOption)) {
            throw new \Exception('At least one "unpublish" option is required in the manifest.');
        }

        switch ($status) {

            case $unpublishOption->getValue():
                return false;
        }

        if ($callback) {
            return $callback($this, inspirecms_content_statuses()->getOption($status));
        }

        return true;
    }

    public function getPublishTime(): ?\Carbon\Carbon
    {
        // If the publish date is in the future, it's not published
        return $this->getLatestPublishedContentVersion()?->pivot?->published_at;
    }

    public function getLatestPublishedTime(): ?\Carbon\Carbon
    {
        return $this->getLatestContentVersionHasPublish()?->pivot?->published_at;
    }

    public function isWebPage(): bool
    {
        return $this->documentType?->isWebPageType() ?? false;
    }

    //region Indexing
    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return InspireCmsConfig::get('indexes.content.index_name', 'content_index');
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing([
            'documentType',
            'parent',
            'webSetting',
        ]);
        $latestVersion = $this->getLatestPublishedContentVersion();
        $data = $this->makeHidden([
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
            $this->getDeletedAtColumn(),
            'document_type',
        ])->toArray();

        unset(
            $data['document_type_id'],
            $data['document_type'],
            $data['published_versions'],
            $data['parent'],
            $data['web_setting'],
        );

        $data['title'] = $this->getTranslations('title');
        $data['is_web'] = $this->documentType?->isWebPageType();

        $data['level'] = $this->getLevel();
        $data['path'] = $this->getFullSlug();

        $data['published_at'] = $latestVersion?->pivot?->published_at?->toIso8601String();
        $data['created_at'] = $this->{$this->getCreatedAtColumn()}?->toIso8601String();
        $data['updated_at'] = $this->{$this->getUpdatedAtColumn()}?->toIso8601String();
        $data['deleted_at'] = $this->{$this->getDeletedAtColumn()}?->toIso8601String();

        $data['document_type'] = [
            'title' => $this->documentType?->title,
            'slug' => $this->documentType?->slug,
        ];

        $data['web_setting'] = [
            'seo' => $this->webSetting->seo ?? [],
            'robots' => $this->webSetting->robots ?? [],
            'redirect_type' => $this->webSetting->redirect_type ?? null,
            'redirect_content_id' => $this->webSetting->redirect_content_id ?? null,
            'redirect_path' => $this->webSetting->redirect_path ?? null,
        ];

        event(new Events\Indexes\IndexingModel($this, $data));

        return $data;
    }

    /**
     * Dispatch the job to make the given models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueMakeSearchable($models)
    {
        // Also index the descendants of the models
        $models = $this->getModelsForIndexSearch($models);
        $this->traitQueueMakeSearchable($models);
    }

    /**
     * Dispatch the job to make the given models unsearchable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueRemoveFromSearch($models)
    {
        // Also index the descendants of the models
        $models = $this->getModelsForIndexSearch($models);
        $this->traitQueueRemoveFromSearch($models);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     */
    protected function getModelsForIndexSearch($models)
    {
        $result = collect();

        foreach ($models as $model) {
            // affecting the "full path" of the model
            if ($model instanceof ContentContract) {
                $result = $result->merge($model->descendants());

            } else {
                $result->push($model);
            }
        }

        return $result;
    }
    //endregion Indexing

    //region Dto
    public static function getDtoClass(): string
    {
        return ContentDto::class;
    }

    public function toDto(...$args)
    {
        $fallbackLocale = $this->getFallbackLocale();
        $locale = $args[0] ?? $fallbackLocale;
        $propertyData = $this->getLatestPublishedPropertyData();

        $dtoClass = static::getDtoClass();

        $availableLocales = array_keys(inspirecms()->getAllAvailableLanguages());

        $dtoParameters = static::prepareDtoParameters($this, $propertyData);

        return $dtoClass::fromTranslatableArray(
            $dtoParameters,
            $locale,
            $fallbackLocale,
            $availableLocales,
        );
    }

    public static function toPreviewDto(array | Model $record, array $propertyData, ?string $locale = null, ?string $fallbackLocale = null, ?Contracts\DocumentType $documentType = null)
    {
        $dtoClass = static::getDtoClass();

        $availableLocales = array_keys(inspirecms()->getAllAvailableLanguages());

        if (is_array($record)) {

            $id = $record['id'] ?? null;

            if (isset($record['id'])) {

                $record = static::query()->findOrFail($id);

                return static::toPreviewDto($record, $propertyData, $locale, $fallbackLocale, $documentType);
            }

            // Is preview while creating a new record

            //todo: load the document type dynamic fields
            if ($documentType && ! $documentType->relationLoaded('fields')) {
                $documentType->setRelation('fields', $documentType->getFieldsThroughQuery()->get());
            }

            $dtoParameters = $record;

            $dtoParameters['propertyTypes'] = collect($documentType?->fields)->map(fn ($field) => $field->toDto());

            $dtoParameters['propertyData'] = $propertyData;

            $seoData = [
                ...($record['webSetting']['seo'] ?? []),
                ...($record['webSetting']['robots'] ?? []),
            ];

            $dtoParameters['seo'] = collect($availableLocales)->mapWithKeys(fn ($locale) => [
                $locale => $seoData,
            ])->all();

        } else {

            $dtoParameters = static::prepareDtoParameters($record, $propertyData, $documentType);

        }

        return $dtoClass::fromTranslatableArray(
            $dtoParameters,
            $locale,
            $fallbackLocale,
            $availableLocales,
        );
    }

    private static function prepareDtoParameters(Model $record, array $propertyData, ?Contracts\DocumentType $documentType = null): array
    {
        $availableLocales = array_keys(inspirecms()->getAllAvailableLanguages());

        // Load the necessary relations
        $record->loadMissing([
            // 'documentType.fieldGroups.fields.group',
            'webSetting',
            'publishedVersions',
        ]);

        if (is_null($documentType)) {
            $record->loadMissing('documentType');
            $documentType ??= $record->documentType;
        }
        if ($documentType && ! $documentType->relationLoaded('fields')) {
            $documentType->setRelation('fields', $documentType->getFieldsThroughQuery()->get());
        }

        $dtoParameters = $record->toArray();

        $dtoParameters['seo'] = collect($availableLocales)->mapWithKeys(fn ($locale) => [
            $locale => $record->webSetting->toDto($locale),
        ])->all();

        $dtoParameters['urls'] = collect($availableLocales)->mapWithKeys(fn ($locale) => [
            $locale => $record->getUrl($locale),
        ])->all();

        $dtoParameters['propertyTypes'] = collect($documentType?->fields)->map(fn ($field) => $field->toDto());

        $dtoParameters['propertyData'] = $propertyData;

        return $dtoParameters;
    }

    //endregion Dto

    //region Scope(s)
    /**
     * Determine if this content is already published.
     */
    public function scopeWhereIsPublished(Builder $query, bool $condition = true)
    {
        $unpublishOption = inspirecms_content_statuses()->getOption('unpublish');
        if (is_null($unpublishOption)) {
            throw new \Exception('At least one "unpublish" option is required in the manifest.');
        }

        if ($condition) {

            $query
                ->whereHas(
                    'publishedVersions',
                    fn ($q) => $q->whereIsPublished()
                )
                ->whereNot('status', $unpublishOption->getValue());

        } else {

            $query
                ->where(
                    fn ($q) => $q
                        ->orWhereDoesntHave(
                            'publishedVersions',
                            fn ($q) => $q->whereIsPublished()
                        )
                        ->orWhere('status', $unpublishOption->getValue())
                );

        }

        return $query;

    }

    public function scopeIsWebPage(Builder $query)
    {
        return $query->whereHas('documentType', fn ($q) => $q->whereIsWebPage());
    }

    //endregion Scope(s)

    //region Attribute(s)
    public function displayStatus(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => inspirecms_content_statuses()->getOption($this->status),
        );
    }
    //endregion Attribute(s)

    //region Nestable
    public function getNestableRootValue(): int | string
    {
        return KeyHelper::generateMinUuid();
    }
    //endregion Nestable

    //region ContentVersion
    protected function prepareContentVersionData(): array
    {
        $data = $this->traitPrepareContentVersionData();
        $data['from']['propertyData'] = $this->getLatestVersionPropertyData();
        $data['to']['propertyData'] = $this->tempRelationData['propertyData'] ?? $this->getLatestVersionPropertyData();
        unset($this->tempRelationData['propertyData']);

        return $data;
    }

    public function setTranslation(string $key, string $locale, $value): Content
    {
        if ($key === 'propertyData') {
            $this->tempRelationData['propertyData'] = $value;

            return $this;
        }

        return $this->traitSetTranslation($key, $locale, $value);
    }

    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed
    {
        if ($key == 'propertyData') {
            return $this->getLatestVersionPropertyData();
        }

        return $this->traitGetTranslation($key, $locale, $useFallbackLocale);
    }

    public function getTranslations(?string $key = null, ?array $allowedLocales = null): array
    {
        if ($key == 'propertyData') {
            return $this->getLatestVersionPropertyData();
        }

        return $this->traitGetTranslations($key, $allowedLocales);
    }

    protected function getContentVersioningAttributes(): array
    {
        return [
            'title',
            'slug',
            'status',
            'document_type_id',
            'parent_id',
        ];
    }
    //endregion ContentVersion

    public static function boot()
    {
        parent::boot();

        static::observe(ContentObserver::class);
    }
}
