<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroupResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListFieldGroup extends BaseListRecords
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('field_group', FieldGroupResource::class);
    }
}
