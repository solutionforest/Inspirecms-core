<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\PageTypeResource\Pages;

use SolutionForest\InspireCms\Filament\Resources\Pages\CreateWithDetailInfoPage;
use SolutionForest\InspireCms\Filament\Resources\Settings\PageTypeResource;

class CreatePageType extends CreateWithDetailInfoPage
{
    protected static bool $canCreateAnother = false;
    
    public static function getResource(): string
    {
        return config('inspirecms-core.resources.page_type', PageTypeResource::class);
    }
}
