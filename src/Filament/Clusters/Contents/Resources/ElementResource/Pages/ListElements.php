<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\Pages;

use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages\BaseContentListPage;

class ListElements extends BaseContentListPage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.element', ElementResource::class);
    }
}
