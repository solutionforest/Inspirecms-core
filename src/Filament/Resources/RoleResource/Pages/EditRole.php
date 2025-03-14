<?php

namespace SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages;

use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditRecord;
use SolutionForest\InspireCms\Filament\Resources\RoleResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class EditRole extends BaseEditRecord
{
    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('role', RoleResource::class);
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
