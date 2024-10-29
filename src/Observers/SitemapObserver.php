<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
use SolutionForest\InspireCms\Models\Contracts\Sitemap;

class SitemapObserver
{
    /**
     * Handle "updated" event.
     *
     * @param  Sitemap|Model  $model  The model instance being updated.
     * @return void
     */
    public function updated(Sitemap | Model $model)
    {
        event(new GenerateSitemap($model, 'updated'));
    }

    /**
     * Handle "deleted" event.
     *
     * @param  Sitemap|Model  $model  The model instance being deleted.
     * @return void
     */
    public function deleted(Sitemap | Model $model)
    {
        event(new GenerateSitemap($model, 'deleted'));
    }
}
