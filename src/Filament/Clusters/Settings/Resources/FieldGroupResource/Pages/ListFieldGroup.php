<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListFieldGroup extends BaseListPage
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
