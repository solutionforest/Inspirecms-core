<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait Publishable
{
    /**
     * The state representing the publishable state.
     */
    protected string $publishableState = 'draft';

    protected array $publishableData = [];

    public static function bootPublishable()
    {
        static::saved(function (self $model) {
            $statusOption = inspirecms_content_statuses()->getOption($model->getPublishableState());
            if ($statusOption && $statusOption->isPublishable()) {

                $data = $model->getPublishableData();
                $data['version_id'] = $model->getLatestContentVersion()?->getKey();
                $model->publishVersionLogs()->create($data);

                $model->resetPublishableData();
            }
            $model->resetPublishableState();
            ray($model)->purple();
        });
    }

    public function publishVersionLogs()
    {
        return $this->hasMany(InspireCmsConfig::getContentPublishVersionModelClass(), 'content_id');
    }

    public function publishedVersions()
    {
        return $this->hasManyThrough(
            InspireCmsConfig::getContentVersionModelClass(),
            InspireCmsConfig::getContentPublishVersionModelClass(),
            'content_id',
            'id',
            'id',
            'version_id'
        );
    }

    public function latestPublishVersion()
    {
        return $this->hasOneThrough(
            InspireCmsConfig::getContentVersionModelClass(),
            InspireCmsConfig::getContentPublishVersionModelClass(),
            'content_id',
            'id',
            'id',
            'version_id'
        )->latest();
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
    public function resetPublishableState(): void
    {
        $this->publishableState = 'draft';
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

    /** {@inheritDoc} */
    public function resetPublishableData(): void
    {
        $this->publishableData = [];
    }

    public function save(array $options = [])
    {
        $status = inspirecms_content_statuses()->getOption($this->getPublishableState());

        $result = $this->performPublishableAction($options, $status);

        event(new \SolutionForest\InspireCms\Events\ChangeContentStatus($result, $status));

        return $result;
    }

    protected function performPublishableAction(array $data, ?ContentStatusOption $option)
    {
        if ($option) {
            $this->status = $option->getValue();
        } else {
            $this->status = inspirecms_content_statuses()->getDefaultValue();
        }

        return parent::save($data);
    }
}
