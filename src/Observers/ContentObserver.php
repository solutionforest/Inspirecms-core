<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Events\Content\ChangeStatus;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentObserver
{
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

    /**
     * Handle "updated" event.
     *
     * @param  Content|Model  $model  The model instance being saving.
     * @return void
     */
    public function updated(Content | Model $model)
    {
        $diff = [$model->getOriginal('status'), $model->getAttribute('status')];

        if ($diff[0] !== $diff[1]) {

            $oldStatus = inspirecms_content_statuses()->getOption($diff[0]);
            $status = inspirecms_content_statuses()->getOption($diff[1]);

            // Unload the relations to prevent large amounts of unnecessary data from being serialized.
            event(new ChangeStatus($model->withoutRelations(), $oldStatus, $status));
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
}
