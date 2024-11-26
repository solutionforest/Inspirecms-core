<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\InspireCms\Events\Content\ChangeStatus;
use SolutionForest\InspireCms\Events\Content\UpdatePath;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentObserver
{
    public function creating(Content | Model $model)
    {
        // Set is default if the first created
        $isDefaultCount = $model->query()->withoutGlobalScope(new SoftDeletingScope)->where('is_default', true)->count();

        if ($isDefaultCount <= 0) {
            $model->is_default = true;
        }
    }

    public function created(Content | Model $model)
    {
        $this->createOrUpdateDefaultPath($model);
    }

    /**
     * Handle "saving" event.
     *
     * @param  Content|Model  $model  The model instance being saving.
     * @return void
     */
    public function saving(Content | Model $model)
    {
        $this->clearCached();
    }

    public function updating(Content | Model $model)
    {
        // Set "is_default" of other content as false if this model is changing to "default"
        if ($model->isDirty(['is_default']) && $model->is_default) {
            $original = $model->newQuery()->where('is_default', true)->whereKeyNot($model->getKey())->get();
            $original->each(function (Content | Model $item) {
                $item->is_default = false;
                $item->save();
            });
        }
    }

    /**
     * Handle "updated" event.
     *
     * @param  Content|Model  $model  The model instance being updated.
     * @return void
     */
    public function updated(Content | Model $model)
    {
        $statusDiff = [$model->getOriginal('status'), $model->getAttribute('status')];

        if ($statusDiff[0] !== $statusDiff[1]) {

            $oldStatus = inspirecms_content_statuses()->getOption($statusDiff[0]);
            $status = inspirecms_content_statuses()->getOption($statusDiff[1]);

            // Unload the relations to prevent large amounts of unnecessary data from being serialized.
            event(new ChangeStatus($model->withoutRelations(), $oldStatus, $status));
        }

        $slugDiff = [$model->getOriginal('slug'), $model->getAttribute('slug')];
        $isDefaultDiff = [$model->getOriginal('is_default'), $model->getAttribute('is_default')];
        if ($slugDiff[0] !== $slugDiff[1] || $isDefaultDiff[0] !== $isDefaultDiff[1]) {
            $this->createOrUpdateDefaultPath($model);
        }
    }

    /**
     * Handle "deleting" event.
     *
     * @param  Content|Model  $model  The model instance being deleting.
     * @return void
     */
    public function deleting(Content | Model $model)
    {
        $this->clearCached();

        $model->sitemap?->setDisable();
        $model->navigation?->setDisable();
    }

    /**
     * Handle "forceDeleting" event.
     *
     * @param  Content|Model  $model  The model instance being forceDeleting.
     * @return void
     */
    public function forceDeleting(Content | Model $model)
    {
        $model->webSetting()->delete();
        $model->sitemap()->delete();

        $model->navigation()->delete();

        $this->clearCached(); // Since the navigation is deleted, we need to clear the cache.
    }

    /**
     * Handle "restoring" event.
     *
     * @param  Content|Model  $model  The model instance being restored.
     * @return void
     */
    public function restoring(Content | Model $model)
    {
        $this->clearCached();

        $model->sitemap?->setEnable();
        $model->navigation?->setEnable();
    }

    protected function clearCached()
    {
        InspireCms::forgetCachedNavigation();
    }

    protected function createOrUpdateDefaultPath(Content | Model $model)
    {
        event(new UpdatePath($model->withoutRelations()));

        if ($model->is_default) {
            return;
        }

        $model->children->each(function ($child) {
            $this->createOrUpdateDefaultPath($child);
        });
    }
}
