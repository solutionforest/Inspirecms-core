<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
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
     * Handle "deleting" event.
     *
     * @param  Content|Model  $model  The model instance being deleting.
     * @return void
     */
    public function deleting(Content | Model $model)
    {
        $this->clearCached();

        //region sitemap
        $model->siteMap?->setDisable();
        //endregion sitemap

        //region navigation
        $model->navigation?->setDisable();
        //endregion sitemap
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
        $model->siteMap()->delete();

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

        // Prevent saving the content version when the model is being restored.
        $this->avoidSaveContentVersion($model);

        // Keep the status of the model when it is being restoring
        // since "restore" event will call "save" method to update the model.
        $publishedState = inspirecms_content_statuses()->getOption($model->status);
        if ($publishedState) {
            $model->setPublishableState($publishedState->getName());
        }

        //region sitemap
        $model->siteMap?->setEnable();
        //endregion sitemap

        //region navigation
        $model->navigation?->setEnable();
        //endregion sitemap
    }

    /**
     * Handle "restored" event.
     *
     * @param  \App\Models\Content|\Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function resotred(Content | Model $model)
    {
        $this->refreshSaveContentVersionFlag($model);
    }

    protected function avoidSaveContentVersion(Content | Model $model)
    {
        // Prevent saving the content version when the model is being restored.
        $model->setCanAddNewConentVersion(false);
    }

    protected function refreshSaveContentVersionFlag(Content | Model $model)
    {
        // Re-enable save the conent version of the model after it has been restored.
        $model->setCanAddNewConentVersion(true);
    }

    protected function clearCached()
    {
        InspireCms::forgetCachedNavigation();
    }
}
