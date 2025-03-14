<?php

namespace SolutionForest\InspireCms\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\UserResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListUsers extends BaseListRecords
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('user', UserResource::class);
    }
}
