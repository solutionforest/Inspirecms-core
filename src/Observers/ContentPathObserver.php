<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\ContentPath;

class ContentPathObserver
{
    /**
     * @param  ContentPath&Model  $model
     * @return void
     */
    public function saved($model)
    {
        $content = $model->content;
        if ($content) {
            $segmentProvider = ContentSegmentFactory::create();
            $content->children()->get()->each(
                fn (Content | Model $child) => $child->path()->updateOrCreate([], [
                    'value' => $segmentProvider->getPath($child),
                ])
            );
        }

    }
}
