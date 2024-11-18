<?php

namespace SolutionForest\InspireCms\Listeners\Content;

use Laravel\Scout\Searchable;
use SolutionForest\InspireCms\Events\Content\DispatchIndexModel;
use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Models\Contracts\Content;
use Spatie\EloquentSortable\EloquentModelSortedEvent;

class ProcessRefreshIndex
{
    public function handle(DispatchIndexModel | EloquentModelSortedEvent $event)
    {
        foreach ($this->getModels() as $modelClass) {
            $this->processChunkedModels($modelClass);
        }
    }

    protected function getModels()
    {
        return array_filter([
            ModelManifest::get(Content::class),
        ], fn ($model) => ! is_null($model) && class_exists($model));
    }

    protected function getChunkSize()
    {
        return 50;
    }

    protected function processChunkedModels(string $modelClass)
    {
        $model = app($modelClass);

        if (! in_array(Searchable::class, class_uses($model))) {
            return;
        }

        $chunkSize = $this->getChunkSize();

        $model::removeAllFromSearch();

        $model::chunk($chunkSize, function ($models) {
            foreach ($models as $model) {
                $model->searchable();
            }
        });
    }
}
