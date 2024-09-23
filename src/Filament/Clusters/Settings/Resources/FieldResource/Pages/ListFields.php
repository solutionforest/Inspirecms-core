<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\CreateAction;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource;

class ListFields extends ListRecords
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
