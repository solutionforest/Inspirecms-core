<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;

use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages\BaseContentCreateChildrentPage;

class CreateChildrenPage extends BaseContentCreateChildrentPage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
