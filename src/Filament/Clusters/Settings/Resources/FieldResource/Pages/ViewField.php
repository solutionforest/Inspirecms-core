<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource;

class ViewField extends BaseViewPage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.field', FieldResource::class);
    }
}
