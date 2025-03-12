<?php

namespace SolutionForest\InspireCms\Observers;

use SolutionForest\InspireCms\Facades\KeyValueCache;
use SolutionForest\InspireCms\Models\Contracts\KeyValue;

class KeyValueObserver
{
    /**
     * @param  KeyValue&Model  $model
     * @return void
     */
    public function created($model)
    {
        $this->clearCached($model);
    }

    /**
     * @param  KeyValue&Model  $model
     * @return void
     */
    public function updated($model)
    {
        $this->clearCached($model);
    }

    /**
     * @param  KeyValue&Model  $model
     * @return void
     */
    public function deleted($model)
    {
        $this->clearCached($model);
    }

    protected function clearCached(KeyValue $model)
    {
        KeyValueCache::forget($model->key);
    }
}
