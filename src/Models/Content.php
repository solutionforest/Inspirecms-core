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
use Illuminate\Support\Arr;
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
use SolutionForest\InspireCms\Support\Models\Concerns\HasRecursiveRelationships;

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
    use HasRecursiveRelationships;
    use HasUuids;
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
            'ancestorsAndSelf',
        ]);
        $latestVersion = $this->getLatestPublishedContentVersion();
        $data = $this->withoutRelations()->makeHidden([
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
            $this->getDeletedAtColumn(),
            'document_type_id',
        ])->toArray();

        // dd($this->ancestorsAndSelf, $this->ancestorsAndSelf->pluck('depth','slug'), $this->descendantsAndSelf);

        $data['title'] = $this->getTranslations('title');
        $data['is_web'] = intval($this->documentType?->isWebPageType() ?? false);

        $data['level'] = collect($this->ancestorsAndSelf)->pluck('depth')->first();
        $data['full_path'] = $this->getFullSlug();

        $data['published_at'] = $latestVersion?->pivot?->published_at?->toIso8601String();
        $data['created_at'] = $this->{$this->getCreatedAtColumn()}?->toIso8601String();
        $data['updated_at'] = $this->{$this->getUpdatedAtColumn()}?->toIso8601String();
        $data['deleted_at'] = $this->{$this->getDeletedAtColumn()}?->toIso8601String();

        $data['document_type'] = [
            'title' => $this->documentType?->title,
            'slug' => $this->documentType?->slug,
        ];

        event(new Events\Indexes\IndexingModel($this, $data));

        return $data;
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPublished();
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
                $result = $result
                    ->push($model)
                    ->concat($model->children()->get())
                    ->unique($this->getKeyName());

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

        return $dtoClass::make(
            $this,
            $propertyData,
            $locale,
        );
    }

    public static function toPreviewDto(array | Model $record, array $propertyData, ?string $locale = null, ?Contracts\DocumentType $documentType = null)
    {
        $dtoClass = static::getDtoClass();

        // create/edit form
        if (is_array($record)) {

            $relationships = [
                'webSetting',
                'children',
            ];

            $tmpModel = new static([
                ...Arr::except($record, $relationships),
            ]);

            $tmpModel->setRelation('documentType', $documentType);

            foreach ($relationships as $relationship) {
                $relationshipData = $record[$relationship] ?? [];
                switch ($relationship) {
                    case 'children':
                        // fetch key from create/edit form
                        $relationshipModel = static::query()->findMany($relationshipData);

                        break;
                    default:
                        $relationshipModel = app(static::class)->{$relationship}()->getRelated()->make($relationshipData);

                        break;
                }
                $tmpModel->setRelation($relationship, $relationshipModel);
            }

            return $dtoClass::make(
                $tmpModel,
                $propertyData,
                $locale,
            );
        }

        return $dtoClass::make(
            $record,
            $propertyData,
            $locale,
        );
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
            get: fn ($value) => $this->status ? inspirecms_content_statuses()->getOption($this->status) : null,
        );
    }
    //endregion Attribute(s)

    //region HasRecursiveRelationships
    public function getCustomPaths()
    {
        return [
            [
                'name' => 'slug_path',
                'column' => 'slug',
                'separator' => '/',
            ],
        ];
    }

    public function getPathSeparator(): string
    {
        return '/';
    }

    public function getParentKeyName()
    {
        return 'parent_id';
    }

    public function getRootLevelParentId()
    {
        return KeyHelper::generateMinUuid();
    }
    //endregion HasRecursiveRelationships

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
