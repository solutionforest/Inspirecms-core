<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroupResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreateRecord;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateFieldGroup extends BaseCreateRecord
{
    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('field_group', FieldGroupResource::class);
    }
}
