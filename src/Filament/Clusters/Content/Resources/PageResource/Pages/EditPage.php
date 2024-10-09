<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentEditPage;

class EditPage extends BaseContentEditPage
{
    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.page', PageResource::class);
    }
}
