<?php

namespace SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages;

use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreateRecord;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateNavigation extends BaseCreateRecord
{
    use Translatable;

    protected static bool $canCreateAnother = true;

    public function getActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }
}
