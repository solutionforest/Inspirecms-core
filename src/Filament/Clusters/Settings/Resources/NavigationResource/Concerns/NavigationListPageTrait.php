<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Concerns;

use Filament\Resources\Components\Tab;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages\ListNavigationTable;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages\ListNavigationTree;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;

trait NavigationListPageTrait
{
    public function updatingActiveTab($value): void
    {
        if ($value == 'tree') {
            $value = 'index';
        }
        $url = FilamentResourceHelper::attemptToGetUrl(static::getResource(), $value, [] , false);
        $this->redirect($url);
    }

    public function getTabs(): array
    {
        $pages = ['index', 'table'];

        return collect($pages)->mapWithKeys(
            function ($page) {
                $key = ($page == 'index' ? 'tree' : $page);
                return [
                    $key => Tab::make()
                        ->label(__('inspirecms::inspirecms.' . $key)),
                ];
            })->all();
    }
 
    public function getDefaultActiveTab(): string | int | null
    {
        switch (true) {
            case $this instanceof ListNavigationTree:
                return 'tree';
            case $this instanceof ListNavigationTable:
                return 'table';
            default:
                return null;
        }
    }
}
