<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;

class CreateRole extends BaseCreatePage
{
    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.role', RoleResource::class);
    }
}
