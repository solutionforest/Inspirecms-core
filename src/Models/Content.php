<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
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
    use Concerns\CanLockContent;
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

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $table = 'content';

    public function newCollection(array $models = [])
    {
        return new \SolutionForest\InspireCms\Collection\ContentCollection($models);
    }

    public function path()
    {
        return $this->hasOne(InspireCmsConfig::getContentPathModelClass(), 'key', 'id');
    }

    public function documentType()
    {
        return $this->belongsTo(InspireCmsConfig::getDocumentTypeModelClass(), 'document_type_id');
    }

    public function sitemap()
    {
        return $this->morphOne(InspireCmsConfig::getSitemapModelClass(), 'model');
    }

    public function trashedParent()
    {
        return $this->parent()->withTrashed();
    }

    public function navigation()
    {
        return $this->hasOne(InspireCmsConfig::getNavigationModelClass(), 'content_id');
    }

    public function routes()
    {
        return $this->hasMany(InspireCmsConfig::getContentRouteModelClass(), 'content_id');
    }

    public function getUrl($locale = null)
    {
        $path = ContentSegmentFactory::create()->getUrlSegment($this, $locale);

        if (is_null($path)) {
            return null;
        }

        return url($path);
    }

    public function isPublished(): bool
    {
        $publishedAt = $this->getPublishTime();
        $status = $this->status;

        // If there's no publish date, it's not published
        if (is_null($publishedAt)) {
            return false;
        }

        $unpublishOption = ContentStatusManifest::getOption('unpublish');
        if (is_null($unpublishOption)) {
            throw new \Exception('At least one "unpublish" option is required in the manifest.');
        }

        if ($status == $unpublishOption->getValue()) {
            return false;
        }

        return $publishedAt->isPast();
    }

    public function isWebPage()
    {
        return $this->documentType?->isWebPageType() ?? false;
    }

    public function setAsDefault()
    {
        $this->is_default = true;
        $this->save();
    }

    // region Dto
    public static function getDtoClass()
    {
        return ContentDto::class;
    }

    /**
     * @return \SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableModelDto | ContentDto
     */
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
            $this->getPublishTime(),
        );
    }

    /**
     * @return \SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableModelDto | ContentDto
     */
    public static function toPreviewDto($record, $propertyData, $locale = null, $documentType = null)
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
                now(),
            );
        }

        return $dtoClass::make(
            $record,
            $propertyData,
            $locale,
            now(),
        );
    }

    // endregion Dto

    // region Scope(s)
    /**
     * Determine if this content is already published.
     */
    public function scopeWhereIsPublished($query, bool $condition = true)
    {
        $unpublishOption = ContentStatusManifest::getOption('unpublish');
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

    public function scopeWhereIsWebPage($query, bool $condition = true)
    {
        return $query->whereHas('documentType', fn ($q) => $q->whereIsWebPage($condition));
    }

    public function scopeWhereIsDefault($query, bool $condition = true)
    {
        return $query->where('is_default', $condition);
    }
    // endregion Scope(s)

    // region Attribute(s)
    public function displayStatus(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $statusKey = $this->status;
                if (is_null($statusKey)) {
                    return null;
                }

                return ContentStatusManifest::getOption($statusKey);
            }
        );
    }
    // endregion Attribute(s)

    // region HasRecursiveRelationships
    public function getCustomPaths()
    {
        return [
            [
                'name' => 'slug_path',
                'column' => 'slug',
                'separator' => '/',
            ],
            [
                'name' => 'reverse_slug_path',
                'column' => 'slug',
                'separator' => '/',
                'reverse' => true,
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
    // endregion HasRecursiveRelationships

    // region ContentVersion
    protected function prepareContentVersionData(): array
    {
        $data = $this->traitPrepareContentVersionData();
        $data['from']['propertyData'] = $this->getLatestVersionPropertyData();
        $data['to']['propertyData'] = $this->tempRelationData['propertyData'] ?? $this->getLatestVersionPropertyData();
        unset($this->tempRelationData['propertyData']);

        if (isset($data['to']['status']) && $toStatus = inspirecms_content_statuses()->getOption($data['to']['status'])) {
            $data['to']['status'] = $toStatus->getName();
        }
        if (isset($data['from']['status']) && $fromStatus = inspirecms_content_statuses()->getOption($data['from']['status'])) {
            $data['from']['status'] = $fromStatus->getName();
        }

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
            'parent_id',
        ];
    }
    // endregion ContentVersion

    protected static function boot()
    {
        parent::boot();

        static::observe(ContentObserver::class);
    }
}
