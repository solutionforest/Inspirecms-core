<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;

class ContentVersionObserver
{
    /**
     * @param  ContentVersion&Model  $model
     * @return void
     */
    public function creating($model)
    {
        $model->created_at = $model->freshTimestamp();
    }

    /**
     * @param  ContentVersion&Model  $model
     * @return void
     */
    public function deleting($model)
    {
        $model->publishLog()->delete();
    }
}
