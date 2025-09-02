<?php

namespace SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages;

use Filament\Actions\EditAction;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewRecord;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ViewNavigation extends BaseViewRecord
{
    use Translatable;

    public function getActions(): array
    {
        return [
            LocaleSwitcher::make(),
            EditAction::make()->iconButton(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }
}
