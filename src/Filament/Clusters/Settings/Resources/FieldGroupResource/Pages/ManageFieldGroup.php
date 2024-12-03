<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseManagePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;

class ManageFieldGroup extends BaseManagePage
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.field_group', FieldGroupResource::class);
    }
}
