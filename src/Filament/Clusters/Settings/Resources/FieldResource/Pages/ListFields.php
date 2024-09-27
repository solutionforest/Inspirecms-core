<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource;

class ListFields extends BaseListPage
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.field', FieldResource::class);
    }
}
