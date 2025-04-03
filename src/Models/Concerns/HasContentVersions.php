<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;
use SolutionForest\InspireCms\Models\Scopes\ContentVersionDetailScope;
use SolutionForest\InspireCms\Observers\HasContentVersionsObserver;

trait HasContentVersions
{
    /**
     * The state representing the publishable state.
     */
    protected string $publishableState = 'draft';

    protected ?string $versioningEvent = null;

    protected array $preloadContentVersionData = [];

    protected array $publishableData = [];

    public static function bootHasContentVersions()
    {
        static::observe(new HasContentVersionsObserver);
    }

    /** {@inheritDoc} */
    public function contentVersions()
    {
        return $this->hasMany(InspireCmsConfig::getContentVersionModelClass(), 'content_id');
    }

    /** {@inheritDoc} */
    public function publishVersionLogs()
    {
        return $this->hasMany(InspireCmsConfig::getContentPublishVersionModelClass(), 'content_id');
    }

    /** {@inheritDoc} */
    public function publishedVersions()
    {
        return $this
            ->belongsToMany(
                InspireCmsConfig::getContentVersionModelClass(),
                InspireCmsConfig::getContentPublishVersionTableName(),
                'content_id',
                'version_id'
            )
            ->withPivot('published_at')
            ->orderBy('published_at', 'desc')
            ->using(InspireCmsConfig::getContentPublishVersionModelClass());
    }

    public function latestContentVersion()
    {
        return $this->hasOne(InspireCmsConfig::getContentVersionModelClass(), 'content_id')->latestOfMany();
    }

    public function getPublishedVersions()
    {
        if (! $this->relationLoaded('publishedVersions')) {
            $this->loadMissing('publishedVersions');
        }

        return collect($this->publishedVersions);
    }

    protected function getOrderedPublishedVersions(): Collection
    {
        return $this->getPublishedVersions()->sortByDesc('pivot.published_at');
    }

    public function getLatestContentVersionHasPublish()
    {
        return $this->getOrderedPublishedVersions()->first();
    }

    public function getLatestPublishedContentVersion()
    {
        return $this->getOrderedPublishedVersions()->where(fn ($version) => $version?->pivot?->published_at?->isPast())->first();
    }

    /** {@inheritDoc} */
    public function getPublishTime()
    {
        // If the publish date is in the future, it's not published
        return $this->getLatestPublishedContentVersion()?->pivot?->published_at;
    }

    /** {@inheritDoc} */
    public function getLatestPublishedTime()
    {
        return $this->getLatestContentVersionHasPublish()?->pivot?->published_at;
    }

    /** {@inheritDoc} */
    public function getLatestPublishedPropertyData()
    {
        // Already load via ContentVersionDetailScope
        if ($this->hasAttribute('__version_details') && $this->hasAttribute('__version_data')) {
            try {

                $lastPublishedVersionId = collect($this->__version_details)
                    ->sortByDesc(fn ($item) => strtotime($item['dt']))
                    ->where('status', 'publish')
                    ->pluck('id')
                    ->first();

                $propData = collect($this->__version_data)
                    ->where(fn ($arr, $key) => $key == $lastPublishedVersionId)
                    ->pluck('propertyData')->first() ?? [];

                if (is_array($propData)) {
                    return $propData;
                } elseif (is_string($propData)) {
                    return json_decode($propData, true);
                }

            } catch (\Throwable $th) {
                // Fallback to load via publishedVersions
            }
        }

        $latestContentVersion = $this->getLatestPublishedContentVersion();

        return $this->mutateLatestVersionPropertyData($latestContentVersion);
    }

    /** {@inheritDoc} */
    public function getLatestVersionPropertyData()
    {
        // Already load via ContentVersionDetailScope
        if ($this->hasAttribute('__version_details') && $this->hasAttribute('__version_data')) {
            try {

                $lastVersionId = collect($this->__version_details)
                    ->sortByDesc(fn ($item) => strtotime($item['dt']))
                    ->pluck('id')
                    ->first();

                $propData = collect($this->__version_data)
                    ->where(fn ($arr, $key) => $key == $lastVersionId)
                    ->pluck('propertyData')->first() ?? [];

                if (is_array($propData)) {
                    return $propData;
                } elseif (is_string($propData)) {
                    return json_decode($propData, true);
                }

            } catch (\Throwable $th) {
                // Fallback to load via latestContentVersion
            }
        }

        $this->loadMissing('latestContentVersion');

        $latestContentVersion = $this->latestContentVersion;

        return $this->mutateLatestVersionPropertyData($latestContentVersion);
    }

    /** {@inheritDoc} */
    public function setPublishableState(string $state)
    {
        $this->publishableState = $state;
    }

    /** {@inheritDoc} */
    public function getPublishableState()
    {
        return $this->publishableState;
    }

    /** {@inheritDoc} */
    public function setVersioningEvent(string $event)
    {
        $this->versioningEvent = $event;
    }

    /** {@inheritDoc} */
    public function getVersioningEvent()
    {
        return $this->versioningEvent;
    }

    /** {@inheritDoc} */
    public function setPublishableData(array $data)
    {
        $this->publishableData = $data;
    }

    /** {@inheritDoc} */
    public function getPublishableData()
    {
        return $this->publishableData;
    }

    public function save(array $options = [])
    {
        $status = inspirecms_content_statuses()->getOption($this->getPublishableState());

        return $this->performPublishableAction($options, $status);
    }

    /** {@inheritDoc} */
    public function preloadContentVersionData()
    {
        $this->preloadContentVersionData = $this->prepareContentVersionData();
    }

    /** {@inheritDoc} */
    public function getPreloadVersionData()
    {
        return $this->preloadContentVersionData;
    }

    /** {@inheritDoc} */
    public function resetContentVersionState()
    {
        $this->resetPublishableData();
        $this->resetPublishableState();
        $this->resetContentVersionData();
    }

    // region Helper(s)
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

    protected function getContentVersioningAttributes(): array
    {
        return $this->contentVersionAttributes ?? [];
    }

    protected function prepareContentVersionData(): array
    {
        $modelIsTranslatable = in_array(\Spatie\Translatable\HasTranslations::class, class_uses_recursive($this));

        return collect($this->getContentVersioningAttributes())
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
                $carry['event_name'] = $this->getVersioningEvent();
                $carry['publish_state'] = $this->getPublishableState();

                return $carry;
            });
    }

    protected function resetContentVersionData(): void
    {
        $this->preloadContentVersionData = [];
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
    // endregion Helper(s)
}
