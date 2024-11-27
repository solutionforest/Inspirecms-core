<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
use SolutionForest\InspireCms\Models\Contracts\Sitemap;

class SitemapObserver
{
    /**
     * @param  Sitemap&Model  $model
     * @return void
     */
    public function created($model)
    {
        $this->dispatchGenerateSitemapEvent($model, 'created');
    }

    /**
     * @param  Sitemap&Model  $model
     * @return void
     */
    public function updated($model)
    {
        $this->dispatchGenerateSitemapEvent($model, 'updated');
    }

    /**
     * @param  Sitemap&Model  $model
     * @return void
     */
    public function deleted($model)
    {
        $this->dispatchGenerateSitemapEvent($model, 'deleted');
    }

    protected function dispatchGenerateSitemapEvent($model, $action)
    {
        event(new GenerateSitemap(get_class($model), $model?->getKey(), $action));
    }
}
