<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateFieldGroup extends BaseCreatePage
{
    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('field_group', FieldGroupResource::class);
    }
}
