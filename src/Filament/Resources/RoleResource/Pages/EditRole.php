<?php

namespace SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditRecord;
use SolutionForest\InspireCms\Filament\Resources\RoleResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class EditRole extends BaseEditRecord
{
    public function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
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
        return FilamentIcon::resolve('inspirecms::edit') ?? FilamentIcon::resolve('actions::edit-action');
    }
}
