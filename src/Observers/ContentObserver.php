<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentObserver
{
    /**
     * Handle the Content "restoring" event.
     *
     * @param  Content|Model  $model  The model instance being restored.
     * @return void
     */
    public function restoring(Content | Model $model)
    {
        // Prevent auditing of the model when it is being restored.
        $model->setCanAddNewConentVersion(false);

        // Keep the status of the model when it is being restoring
        // since "restore" event will call "save" method to update the model.
        $publishedState = inspirecms_content_statuses()->getOption($model->status);
        if ($publishedState) {
            $model->setPublishableState($publishedState->getName());
        }
    }

    /**
     * Handle the Content "restored" event.
     *
     * @param  \App\Models\Content|\Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function resotred(Content | Model $model)
    {
        // Re-enable auditing of the model after it has been restored.
        $model->setCanAddNewConentVersion(true);
    }
}
