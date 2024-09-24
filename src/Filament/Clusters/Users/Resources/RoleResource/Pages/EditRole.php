<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource\Pages;

use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditPage;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;

class EditRole extends BaseEditPage
{
    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.role', RoleResource::class);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabIcon(): ?string
    {
        return FilamentIcon::resolve('actions::edit-action') ?? 'heroicon-m-pencil-square';
    }
}
