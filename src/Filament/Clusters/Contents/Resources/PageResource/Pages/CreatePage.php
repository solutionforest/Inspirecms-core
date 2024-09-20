<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;

use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages\BaseContentCreatePage;

class CreatePage extends BaseContentCreatePage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
