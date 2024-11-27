<?php

namespace SolutionForest\InspireCms\Listeners\Content;

use SolutionForest\InspireCms\Events\Content\UpdatePath;
use SolutionForest\InspireCms\Helpers\UrlHelper;

class ProcessContentPath
{
    public function handleUpsert(UpdatePath $event)
    {
        $data = [
            'slug_path' => $event->slugPath ?? $event->model->generateSlugPath(),
        ];

        if (blank($data['slug_path'])) {
            $data['slug_path'] = '/';
        }

        $data['encoded_path'] = UrlHelper::getShortener($data['slug_path']);

        $event->model->path()->updateOrCreate(
            [],
            $data
        );
    }
}
