<?php

namespace SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreateRecord;
use SolutionForest\InspireCms\Filament\Resources\RoleResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateRole extends BaseCreateRecord
{
    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('role', RoleResource::class);
    }
}
