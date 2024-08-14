<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\PageTypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use SolutionForest\InspireCms\Filament\Resources\Settings\PageTypeResource;

class ListPageTypes extends ListRecords
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page_type', PageTypeResource::class);
    }
}