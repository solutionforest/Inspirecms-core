<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Events\Content\UpsertRoute;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\Models\Contracts\Content;
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
    public function saved($model)
    {
        $content = $model->content;
        if ($content) {

            $segmentProvider = ContentSegmentFactory::create();

            $content->children()->get()->load('routes')->each(function (Content | Model $child) use ($segmentProvider) {

                $uri = $segmentProvider->getSegment($child);

                $currentRoutes = collect($child->routes->where('is_default_pattern', true))
                    ->map(fn (Model $model) => $model->toArray())
                    ->map(function (array $data) use ($uri) {
                        $data['uri'] = $uri;

                        return $data;
                    })
                    ->all();

                event(new UpsertRoute($child->withoutRelations(), $currentRoutes));
            });
        }
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
