<?php

namespace SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages;

use Filament\Actions\CreateAction;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\RoleResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListRoles extends BaseListRecords
{
    public function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('role', RoleResource::class);
    }
}
