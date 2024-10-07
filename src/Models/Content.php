<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Database\Factories\ContentFactory;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\Models\Contracts\Content as ContentContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class Content extends BaseModel implements ContentContract
{
    use Concerns\BelongToCmsNestableTree;
    use Concerns\HasAuthor;
    use Concerns\HasContentVersions {
        prepareAuditData as protected traitPrepareAuditData;
    }
    use Concerns\HasTemplates;
    use Concerns\NestableTrait;
    use Concerns\HasTranslations {
        setTranslation as protected traitSetTranslation;
        getTranslation as protected traitGetTranslation;
        getTranslations as protected traitGetTranslations;
    }
    use HasUuids;
    use SoftDeletes;
    use HasFactory;

    protected $guarded = ['id'];

    public ?array $translatable = ['propertyData'];

    protected ?array $tempPropertyData = [];

    protected $casts = [];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getDocumentTypeModelClass(), 'document_type_id');
    }

    /**
     * Generate a full slug base on parent.
     */
    public function generateFullSlug(): string
    {
        $ancestors = $this->ancestors();
        $slugs = [];
        foreach ($ancestors as $ancestor) {
            $slugs[] = $ancestor->slug;
        }
        $slugs[] = $this->slug;

        return implode('/', $slugs);
    }

    public function isPublished(?\Closure $callback = null): bool
    {
        $latestContentVersion = $this->getLatestPublishedContentVersion();
        /** @var ?\Carbon\Carbon */
        $publishedAt = $latestContentVersion?->pivot?->published_at;
        $status = $this->status;

        // If there's no publish date, it's not published
        if (is_null($publishedAt)) {
            return false;
        }

        // Check if the publish date is in the past
        if ($publishedAt->isPast()) {

            $unpublishOption = inspirecms_content_statuses()->getOption('unpublish');
            if (is_null($unpublishOption)) {
                throw new \Exception('At least one "unpublish" option is required in the manifest.');
            }

            switch ($status) {

                case $unpublishOption->getValue():
                    return false;

                default:
                    if ($callback) {
                        return $callback($this, inspirecms_content_statuses()->getOption($status));
                    }

                    return true;
            }
        }

        // If the publish date is in the future, it's not published
        return false;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (blank($model->{$model->getNestableParentIdColumn()})) {
                $model->{$model->getNestableParentIdColumn()} = $model->fallbackParentId();
            }
        });
        static::deleting(function (self $model) {
            $model->children()->delete();
        });
        static::forceDeleting(function (self $model) {
            $model->children()->forceDelete();
        });
    }

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
                ->where('published_at', '<', now())
                ->whereNot('status', $unpublishOption->getValue());

        } else {

            $query
                ->orWhereNull('published_at')
                ->orWhereNot('published_at', '<', now())
                ->orWhere('status', $unpublishOption->getValue());
        }

    }

    public function scopeIsRootLevel(Builder $query, bool $condition = true): void
    {
        $rootValue = $this->getNestableRootValue();
        $query->where('parent_id', $condition ? $rootValue : '!=', $rootValue);
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
    protected function getParentId()
    {
        return $this->{$this->getNestableParentIdColumn()} ?? $this->fallbackParentId();
    }

    public function getNestableParentIdColumn(): string
    {
        return 'parent_id';
    }

    protected function fallbackParentId()
    {
        return $this->getNestableRootValue();
    }

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

    //region Audit
    protected function prepareAuditData(): array
    {
        $data = $this->traitPrepareAuditData();
        $data['from']['propertyData'] = $this->getLatestVersionPropertyData();
        $data['to']['propertyData'] = $this->tempPropertyData ?? [];

        $this->tempPropertyData = [];

        return $data;
    }

    public function setTranslation(string $key, string $locale, $value): Content
    {
        if ($key === 'propertyData') {
            $this->tempPropertyData = $value;
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

    public function getTranslations(string $key = null, array $allowedLocales = null): array
    {
        if ($key == 'propertyData') {
            return $this->getLatestVersionPropertyData();
        }
        return $this->traitGetTranslations($key, $allowedLocales);
    }

    protected function getAuditAttributes(): array
    {
        return [
            'title',
            'slug',
            'status',
            'document_type_id',
            'parent_id',
        ];
    }
    //endregion Audit
}
