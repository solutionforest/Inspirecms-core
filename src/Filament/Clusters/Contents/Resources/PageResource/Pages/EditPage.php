<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;

use SolutionForest\InspireCms\Filament\Clusters\Contents\Contracts\HasPublishForm;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages\BaseContentEditPage;

class EditPage extends BaseContentEditPage implements HasPublishForm
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
