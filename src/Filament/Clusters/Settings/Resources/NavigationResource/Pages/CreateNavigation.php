<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateNavigation extends BaseCreatePage
{
    use Translatable;

    public function getActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }
}
