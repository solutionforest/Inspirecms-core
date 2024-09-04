<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;

class CreateRole extends CreateRecord
{
    public function getActions(): array
    {
        return [];
    }
    
    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.role', RoleResource::class);
    }
}
