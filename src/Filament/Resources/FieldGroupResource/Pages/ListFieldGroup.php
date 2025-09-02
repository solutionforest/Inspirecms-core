<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroupResource\Pages;

use Filament\Actions\CreateAction;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListFieldGroup extends BaseListRecords
{
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('field_group', FieldGroupResource::class);
    }
}
