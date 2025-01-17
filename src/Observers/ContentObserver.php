<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Content\SegmentProviderInterface;
use SolutionForest\InspireCms\Events\Content\ChangeStatus;
use SolutionForest\InspireCms\Events\Content\UpsertRoute;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentObserver
{
    /**
     * @param  Content&Model  $model
     * @return void
     */
    public function creating($model)
    {
        // Set is default if the first created
        $isDefaultCount = $this->getTotalDefaultContent($model->newQuery());

        if ($isDefaultCount <= 0) {
            $model->is_default = true;
        }
    }

    /**
     * @param  Content&Model  $model
     * @return void
     */
    public function created($model)
    {
        $provider = ContentSegmentFactory::create();

        $model->path()->updateOrCreate([], [
            'value' => $provider->getPath($model),
        ]);

        $this->createDefaultRoute($model, $provider);
    }

    /**
     * @param  Content&Model  $model
     * @return void
     */
    public function saving($model)
    {
        $this->clearCached();
    }

    /**
     * @param  Content&Model  $model
     * @return void
     */
    public function updating($model)
    {
        // Set "is_default" of other content as false if this model is changing to "default"
        if ($model->isDirty(['is_default']) && $model->is_default) {
            $otherDefaultContent = $this->getOtherDefaultContent($model);
            $otherDefaultContent->each(function (Content | Model $item) {
                $item->is_default = false;
                $item->save();
            });
        }
    }

    /**
     * @param  Content&Model  $model
     * @return void
     */
    public function updated($model)
    {
        $statusDiff = [$model->getOriginal('status'), $model->getAttribute('status')];

        if ($statusDiff[0] !== $statusDiff[1]) {

            $oldStatus = inspirecms_content_statuses()->getOption($statusDiff[0]);
            $status = inspirecms_content_statuses()->getOption($statusDiff[1]);

            // Unload the relations to prevent large amounts of unnecessary data from being serialized.
            event(new ChangeStatus($model->withoutRelations(), $oldStatus, $status));
        }
    }

    /**
     * @param  Content&Model  $model
     * @return void
     */
    public function deleting($model)
    {
        $this->clearCached();

        $model->sitemap?->setDisable();
        $model->navigation?->setDisable();
    }

    /**
     * @param  Content&Model  $model
     * @return void
     */
    public function forceDeleting($model)
    {
        $model->webSetting()->delete();
        $model->sitemap()->delete();

        $model->navigation()->delete();

        $this->clearCached(); // Since the navigation is deleted, we need to clear the cache.
    }

    /**
     * @param  Content&Model  $model
     * @return void
     */
    public function restoring($model)
    {
        $this->clearCached();

        $model->sitemap?->setEnable();
        $model->navigation?->setEnable();

        // Have other default content and this content is default
        if ($model->is_default) {
            $otherDefaultContent = $this->getOtherDefaultContent($model);
            if ($otherDefaultContent->isNotEmpty()) {
                $model->is_default = false;
            }
        }
    }

    protected function clearCached()
    {
        InspireCms::forgetCachedNavigation();
        InspireCms::forgetCachedLanguages();
        InspireCms::forgetCachedContentRoutes();
    }

    /**
     * @param  Content & Model  $model
     * @param  SegmentProviderInterface $provider
     */
    protected function createDefaultRoute($model, $provider)
    {
        if (! $model->isWebPage()) {
            return;
        }

        event(new UpsertRoute(
            $model->withoutRelations(),
            [
                [
                    'language_id' => null,
                    'uri' => $provider->getSegment($model),
                    'is_default_pattern' => true,
                ]
            ]
        ));
    }

    /**
     * @param  \SolutionForest\InspireCms\Models\Contracts\Content|\Illuminate\Database\Eloquent\Model  $original
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getOtherDefaultContent($original)
    {
        return $original->newQuery()
            ->withoutGlobalScopes([])
            ->where('is_default', true)
            ->whereKeyNot($original->getKey())
            ->get();
    }

    /**
     * Get the total count of default content based on the provided query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query  The query builder instance.
     * @return int The total count of default content.
     */
    protected function getTotalDefaultContent($query)
    {
        return $query
            ->withoutGlobalScopes([])
            ->where('is_default', true)
            ->count();
    }
}
