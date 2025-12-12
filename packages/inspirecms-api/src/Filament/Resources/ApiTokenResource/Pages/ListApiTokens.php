<?php

namespace SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource;

class ListApiTokens extends ListRecords
{
    protected static string $resource = ApiTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
