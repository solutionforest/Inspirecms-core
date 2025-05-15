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
        $this->updateChildrenRoutes($model, $content);
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

    protected function updateChildrenRoutes(ContentRoute $contentRoute, ?Content $content): void {

        if (! $contentRoute->is_default_pattern) {
            return;
        }
        
        $segmentProvider = ContentSegmentFactory::create();

        $child = $content->children()
            ->whereHas('routes', fn ($query) => $query
                ->where('is_default_pattern', true)
                ->where('language_id', $contentRoute->language_id)
            )
            ->with(['routes'])
            ->get();

        $child->each(function (Content | Model $child) use ($segmentProvider, $contentRoute) {

            $uri = $segmentProvider->getRouteSegmentWithPrefix($child->slug, $contentRoute->uri);

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
