<?php

namespace SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Concerns\NavigationListPageTrait;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListNavigationTable extends BaseListRecords
{
    use NavigationListPageTrait;
    use Translatable;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }
}
