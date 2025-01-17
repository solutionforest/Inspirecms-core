<?php

namespace SolutionForest\InspireCms\Listeners\Content;

use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Events\Content\UpsertRoute;
use SolutionForest\InspireCms\Facades\InspireCms;

class ProcessContentRoute
{
    public function upsert(UpsertRoute $event)
    {
        $content = $event->content;

        $data = collect($event->data)
            ->filter(fn ($d) => is_array($d))
            ->all();

        if (empty($data)) {
            return;
        }
        $result = [];

        foreach ($data as $d) {
            try {

                if (isset($d['id'])) {

                    $route = $content->routes()->find($d['id']);

                    if ($route) {
                        $route->update(
                            Arr::except($d, ['language_id', 'content_id']),
                        );
                    }

                } else {

                    $route = $content->routes()->updateOrCreate(
                        Arr::only($d, ['language_id']),
                        Arr::except($d, ['language_id', 'content_id']),
                    );
                }

                if (isset($route)) {

                    $result[] = $route;
                }

            } catch (\Throwable $th) {
                // Skip
            }
        }

        if (! empty($event->toRemove)) {
            $deletedCount = $content->routes()->whereIn('id', $event->toRemove)->delete();
        }

        InspireCms::forgetCachedContentRoutes();
    }
}
