<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;

class EditRole extends EditRecord
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

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabIcon(): ?string
    {
        return FilamentIcon::resolve('actions::edit-action') ?? 'heroicon-m-pencil-square';
    }
}
