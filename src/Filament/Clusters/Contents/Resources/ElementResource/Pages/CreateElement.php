<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\Pages;

use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages\BaseContentCreatePage;

class CreateElement extends BaseContentCreatePage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.element', ElementResource::class);
    }
}
