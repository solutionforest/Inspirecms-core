<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\UserResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use SolutionForest\InspireCms\Filament\Resources\Users\UserResource;

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
