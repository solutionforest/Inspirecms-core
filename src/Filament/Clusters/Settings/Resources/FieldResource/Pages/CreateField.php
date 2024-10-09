<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource;

class CreateField extends BaseCreatePage
{
    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.field', FieldResource::class);
    }
}
