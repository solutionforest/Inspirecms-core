<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;

class ListUsers extends ListRecords
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.user', UserResource::class);
    }
}
