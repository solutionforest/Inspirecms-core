<?php

namespace SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages;

use Filament\Actions;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewRecord;
use SolutionForest\InspireCms\Filament\Resources\RoleResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ViewRole extends BaseViewRecord
{
    public function getActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
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
        return FilamentIcon::resolve('inspirecms::view') ?? FilamentIcon::resolve('actions::view-action');
    }
}
