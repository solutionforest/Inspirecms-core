<?php

namespace SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages;

use Filament\Actions\ViewAction;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditRecord;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class EditNavigation extends BaseEditRecord
{
    use Translatable;

    public function getActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ViewAction::make()->iconButton(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }
}
