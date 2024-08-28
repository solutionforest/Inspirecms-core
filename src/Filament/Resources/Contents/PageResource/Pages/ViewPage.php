<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;

class ViewPage extends ViewRecord
{
    public function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
