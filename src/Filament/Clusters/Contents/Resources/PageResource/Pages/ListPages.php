<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;

use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages\BaseContentListPage;

class ListPages extends BaseContentListPage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
