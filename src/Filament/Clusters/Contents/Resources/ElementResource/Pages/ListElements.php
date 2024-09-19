<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource;

class ListElements extends ListRecords
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.element', ElementResource::class);
    }
}
