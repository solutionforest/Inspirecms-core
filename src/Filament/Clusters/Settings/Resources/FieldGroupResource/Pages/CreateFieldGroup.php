<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;

class CreateFieldGroup extends BaseCreatePage
{
    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.field_group', FieldGroupResource::class);
    }
}
