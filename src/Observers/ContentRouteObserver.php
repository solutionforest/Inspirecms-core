<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\ContentRoute;

class ContentRouteObserver
{
    /**
     * @param  ContentRoute&Model  $model
     * @return void
     */
    public function created($model)
    {
        $this->clearCached();
    }

    /**
     * @param  ContentRoute&Model  $model
     * @return void
     */
    public function updated($model)
    {
        $this->clearCached();
    }

    /**
     * @param  ContentRoute&Model  $model
     * @return void
     */
    public function deleted($model)
    {
        $this->clearCached();
    }

    protected function clearCached()
    {
        InspireCms::forgetCachedContentRoutes();
    }
}
