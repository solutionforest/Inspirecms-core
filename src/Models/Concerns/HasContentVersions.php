<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Events\Content as ContentEvents;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait HasContentVersions
{
    /**
     * The state representing the publishable state.
     */
    protected string $publishableState = 'draft';

    protected array $contentVersionData = [];

    protected array $publishableData = [];

    protected bool $canAddContentVersion = true;

    public static function bootHasContentVersions()
    {
        static::saving(function (self $model) {
            $model->contentVersionData = $model->prepareContentVersionData();
        });

        static::saved(function (self $model) {

            if ($model->canAddContentVersion) {

                $statusOption = inspirecms_content_statuses()->getOption($model->getPublishableState());
                $isPublishing = $statusOption && $statusOption->isPublishable();

                $contentVersion = $model->contentVersions()->create([
                    'from_data' => $model->contentVersionData['from'] ?? [],
                    'to_data' => $model->contentVersionData['to'] ?? [],
                    'avoid_to_clean' => $isPublishing,
                ]);

                event(new ContentEvents\VersionCreated($model, $contentVersion, $statusOption, $isPublishing));

                if ($isPublishing) {
                    $data = $model->getPublishableData();
                    $data['version_id'] = $contentVersion->getKey();
                    $publishVersion = $model->publishVersionLogs()->create($data);

                    event(new ContentEvents\PublishVersionCreated($model, $contentVersion, $publishVersion, $statusOption));
                }
            }

            $model->resetPublishableData();
            $model->resetPublishableState();
            $model->resetContentVersionData();
        });

        static::forceDeleting(function (self $model) {
            $model->contentVersions()->delete();
            $model->publishVersionLogs()->delete();
        });
    }

    /** {@inheritDoc} */
    public function contentVersions(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getContentVersionModelClass(), 'content_id');
    }

    /** {@inheritDoc} */
    public function publishVersionLogs(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getContentPublishVersionModelClass(), 'content_id');
    }

    /** {@inheritDoc} */
    public function publishedVersions(): BelongsToMany
    {
        return $this->belongsToMany(
            InspireCmsConfig::getContentVersionModelClass(),
            InspireCmsConfig::getContentPublishVersionTableName(),
            'content_id',
            'version_id'
        )->withPivot('published_at')->orderBy('published_at', 'desc')->using(InspireCmsConfig::getContentPublishVersionModelClass());
    }

    public function latestContentVersion(): HasOne
    {
        return $this->hasOne(InspireCmsConfig::getContentVersionModelClass(), 'content_id')->latestOfMany();
    }

    public function getPublishedVersions(): Collection
    {
        $this->loadMissing('publishedVersions');

        return collect($this->publishedVersions);
    }

    protected function getOrderedPublishedVersions(): Collection
    {
        return $this->getPublishedVersions()->sortByDesc('pivot.published_at');
    }

    public function getLatestContentVersionHasPublish(): ?ContentVersion
    {
        return $this->getOrderedPublishedVersions()->first();
    }

    public function getLatestPublishedContentVersion(): ?ContentVersion
    {
        return $this->getOrderedPublishedVersions()->where(fn ($version) => $version?->pivot?->published_at?->isPast())->first();
    }

    /** {@inheritDoc} */
    public function getLatestPublishedPropertyData(): array
    {
        $latestContentVersion = $this->getLatestPublishedContentVersion();

        return $this->mutateLatestVersionPropertyData($latestContentVersion);
    }

    /** {@inheritDoc} */
    public function getLatestVersionPropertyData(): array
    {
        $this->loadMissing('latestContentVersion');

        $latestContentVersion = $this->latestContentVersion;

        return $this->mutateLatestVersionPropertyData($latestContentVersion);
    }

    /** {@inheritDoc} */
    public function setPublishableState(string $state): void
    {
        $this->publishableState = $state;
    }

    /** {@inheritDoc} */
    public function setCanAddNewConentVersion(bool $canAddContentVersion): void
    {
        $this->canAddContentVersion = $canAddContentVersion;
    }

    /** {@inheritDoc} */
    public function getPublishableState(): string
    {
        return $this->publishableState;
    }

    /** {@inheritDoc} */
    public function setPublishableData(array $data): void
    {
        $this->publishableData = $data;
    }

    /** {@inheritDoc} */
    public function getPublishableData(): array
    {
        return $this->publishableData;
    }

    public function save(array $options = [])
    {
        $status = inspirecms_content_statuses()->getOption($this->getPublishableState());
        $oldStatus = $this->status ?
            inspirecms_content_statuses()->getOption($this->status) :
            null;

        $result = $this->performPublishableAction($options, $status);

        event(new ContentEvents\ChangeStatus($this, $oldStatus, $status));

        return $result;
    }

    //region Helper(s)
    protected function performPublishableAction(array $data, ?ContentStatusOption $option)
    {
        if ($option) {
            $this->status = $option->getValue();
        } else {
            $this->status = inspirecms_content_statuses()->getDefaultValue();
        }

        return parent::save($data);
    }

    protected function mutateLatestVersionPropertyData(?ContentVersion $contentVersion): array
    {
        if (! $contentVersion) {
            return [];
        }

        $data = data_get($contentVersion->to_data ?? [], 'propertyData');

        if (! empty($data)) {
            if (is_string($data)) {
                return json_decode($data, true);
            }

            return $data;
        }

        return [];
    }

    protected function getContentVersioingAttributes(): array
    {
        return $this->contentVersionAttributes ?? [];
    }

    protected function prepareContentVersionData(): array
    {
        $modelIsTranslatable = in_array(\Spatie\Translatable\HasTranslations::class, class_uses_recursive($this));

        return collect($this->getContentVersioingAttributes())
            ->map(function ($attribute) use ($modelIsTranslatable): array {

                $isTranslatable = $modelIsTranslatable && $this->isTranslatableAttribute($attribute);

                $diff = [
                    'attribute' => $attribute,
                    'old' => $this->getOriginal($attribute),
                    'new' => $this->getAttribute($attribute),
                ];

                if ($isTranslatable) {
                    $diff['new'] = $this->getTranslations($attribute);
                }

                return $diff;
            })
            ->reduce(function ($carry, $item) {
                $carry ??= [];
                $carry['from'][$item['attribute']] = $item['old'];
                $carry['to'][$item['attribute']] = $item['new'];

                return $carry;
            });
    }

    protected function resetContentVersionData(): void
    {
        $this->contentVersionData = [];
    }

    protected function resetPublishableData(): void
    {
        $this->publishableData = [];
    }

    /**
     * Reset the publishable state to the default state.
     *
     * This method sets the publishable state back to its default
     * value, e.g. 'draft'.
     */
    protected function resetPublishableState(): void
    {
        $this->publishableState = 'draft';
    }
    //endregion Helper(s)
}
