<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait HasContentVersions
{
    /**
     * The state representing the publishable state.
     */
    protected string $publishableState = 'draft';

    protected array $auditData = [];

    protected array $publishableData = [];

    public static function bootHasContentVersions()
    {
        static::saving(function (self $model) {
            $model->auditData = $model->prepareAuditData();
        });

        static::saved(function (self $model) {
            $contentVersion = $model->contentVersions()->create([
                'from_data' => $model->auditData['from'] ?? [],
                'to_data' => $model->auditData['to'] ?? [],
            ]);

            $statusOption = inspirecms_content_statuses()->getOption($model->getPublishableState());
            if ($statusOption && $statusOption->isPublishable()) {

                $data = $model->getPublishableData();
                $data['version_id'] = $contentVersion->getKey();
                $model->publishVersionLogs()->create($data);

            }

            $model->resetPublishableData();
            $model->resetPublishableState();
            $model->resetAuditData();
        });

        static::forceDeleting(function (self $model) {
            $model->contentVersions()->delete();
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

    /** {@inheritDoc} */
    public function getLatestContentVersion(): ?ContentVersion
    {
        $this->loadMissing('contentVersions');

        return $this->contentVersions->sortByDesc('created_at')->first();
    }

    public function getLatestPublishedContentVersion(): ?ContentVersion
    {
        $this->loadMissing('publishedVersions');

        return $this->publishedVersions->first();
    }

    /** {@inheritDoc} */
    public function getLatestVersionPropertyData(): array
    {
        $latestContentVersion = $this->getLatestContentVersion();

        return $this->mutateLatestVersionPropertyData($latestContentVersion);
    }

    /** {@inheritDoc} */
    public function setPublishableState(string $state): void
    {
        $this->publishableState = $state;
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

        $result = $this->performPublishableAction($options, $status);

        event(new \SolutionForest\InspireCms\Events\ChangeContentStatus($result, $status));

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

    protected function getAuditAttributes(): array
    {
        return $this->auditAttributes ?? [];
    }

    protected function prepareAuditData(): array
    {
        return collect($this->getAuditAttributes())
            ->map(fn ($attribute): array => [
                'attribute' => $attribute,
                'old' => $this->getOriginal($attribute),
                'new' => $this->getAttribute($attribute),
            ])
            ->reduce(function ($carry, $item) {
                $carry ??= [];
                $carry['from'][$item['attribute']] = $item['old'];
                $carry['to'][$item['attribute']] = $item['new'];

                return $carry;
            });
    }

    protected function resetAuditData(): void
    {
        $this->auditData = [];
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
