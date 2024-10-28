<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use SolutionForest\InspireCms\Database\Factories\ContentFactory;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentPathGeneratorFactory;
use SolutionForest\InspireCms\Factories\ContentUrlGeneratorFactory;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\Models\Contracts\Content as ContentContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Concerns\BelongToNestableTree;
use SolutionForest\InspireCms\Support\Models\Concerns\HasAuthor;
use SolutionForest\InspireCms\Support\Models\Concerns\NestableTrait;

class Content extends BaseModel implements ContentContract
{
    use BelongToNestableTree;
    use Concerns\HasContentVersions {
        prepareContentVersionData as protected traitPrepareContentVersionData;
    }
    use Concerns\HasTemplates;
    use Concerns\HasTranslations {
        setTranslation as protected traitSetTranslation;
        getTranslation as protected traitGetTranslation;
        getTranslations as protected traitGetTranslations;
    }
    use HasAuthor;
    use HasFactory;
    use HasUuids;
    use NestableTrait;
    use Searchable;
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

    public function webSetting(): HasOne
    {
        return $this->hasOne(InspireCmsConfig::getContentWebSettingModelClass(), 'content_id');
    }

    public function siteMap(): MorphOne
    {
        return $this->morphOne(InspireCmsConfig::getSiteMapModelClass(), 'model');
    }

    public function withTrashedParent(): BelongsTo
    {
        return $this->parent()->withTrashed();
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
            'publishedVersions',
            'parent',
        ]);
        $latestVersion = $this->getLatestPublishedContentVersion();
        $data = $this->makeHidden([
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
            $this->getDeletedAtColumn(),
            'document_type',
            'published_versions',
        ])->toArray();

        unset(
            $data['document_type_id'],
            $data['document_type'],
            $data['published_versions'],
            $data['parent'],
        );

        $data['title'] = $this->getTranslations('title');
        $data['is_web'] = $this->documentType?->isWebPageType();

        $data['level'] = $this->getLevel();
        $data['path'] = $this->getFullSlug();

        $data['published_at'] = $latestVersion?->pivot?->published_at->toIso8601String();
        $data['created_at'] = $this->{$this->getCreatedAtColumn()}->toIso8601String();
        $data['updated_at'] = $this->{$this->getUpdatedAtColumn()}->toIso8601String();
        $data['deleted_at'] = $this->{$this->getDeletedAtColumn()}?->toIso8601String();

        $data['document_type'] = [
            'title' => $this->documentType?->title,
            'slug' => $this->documentType?->slug,
        ];

        return $data;
    }
    //endregion Indexing

    //region Dto
    public function toDto(...$args)
    {
        return static::getDtoClass()::fromTranslatableModel($this, $args[0] ?? null);
    }

    public static function getDtoClass(): string
    {
        return ContentDto::class;
    }

    public static function toPreviewDto(array | Model $record, array $propertyData, ?string $locale = null, ?string $fallbackLocale = null, ?Contracts\DocumentType $documentType = null)
    {
        $dtoClass = static::getDtoClass();
        $dto = $record instanceof Model ? $dtoClass::fromModel($record) : $dtoClass::fromArray($record);
        if ($dto instanceof ContentDto) {
            $dto->setLocale($locale)->setFallbackLocale($fallbackLocale);

            if ($documentType) {
                $dto->documentType = $documentType->toDto();
            }
            $dto->setPropertyData($propertyData);
        }

        return $dto;
    }
    //endregion Dto

    //region Scope(s)
    /**
     * Determine if this content is already published.
     */
    public function scopeIsPublished(Builder $query, bool $condition = true): void
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

    }

    public function scopeIsWebPage(Builder $query): void
    {
        $query->whereHas('documentType', fn ($q) => $q->whereIsWebPage());
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

    //region Factory
    protected static function newFactory()
    {
        return ContentFactory::new();
    }
    //endregion Factory

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

    protected function getContentVersioingAttributes(): array
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

        static::saving(function (self $model) {
            InspireCms::forgetCachedNavigation();
        });

        static::deleting(function (self $model) {
            InspireCms::forgetCachedNavigation();
        });

        static::restoring(function (self $model) {
            InspireCms::forgetCachedNavigation();
        });
    }
}
