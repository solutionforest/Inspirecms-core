<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;

class ContentVersionObserver
{
    /**
     * Handle "creating" event.
     *
     * @param  ContentVersion|Model  $model  The model instance being creating.
     * @return void
     */
    public function creating(ContentVersion | Model $model)
    {
        $model->created_at = $model->freshTimestamp();
    }

    /**
     * Handle "deleting" event.
     *
     * @param  ContentVersion|Model  $model  The model instance being deleting.
     * @return void
     */
    public function deleting(ContentVersion | Model $model)
    {
        $model->publishLog()->delete();
    }
}
