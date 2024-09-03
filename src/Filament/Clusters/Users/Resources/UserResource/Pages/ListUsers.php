<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;

use Filament\Resources\Pages\ListRecords;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;

class ListUsers extends ListRecords
{
    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.user', UserResource::class);
    }
}
