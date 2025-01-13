<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseManagePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Concerns\NavigationListPageTrait;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListNavigationTable extends BaseManagePage
{
    use NavigationListPageTrait;
    use Translatable;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make()->slideOver()->after(fn () => $this->redirectIntended($this->getUrl())),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }
}
