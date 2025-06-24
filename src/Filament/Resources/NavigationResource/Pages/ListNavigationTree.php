<?php

namespace SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Concerns\NavigationListPageTrait;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListNavigationTree extends BaseListRecords
{
    use NavigationListPageTrait;
    use Translatable;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.pages.list-navigation';

    public function getActions(): array
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

    #[On('refreshAllTree')]
    public function getAllCategories(): array
    {
        return $this->getModel()::query()->groupBy('category')->pluck('category')->toArray();
    }

    public function updatingActiveLocale($value)
    {
        $this->dispatch('refreshAllTree');
    }

    public function getNavigationTreeData($category): array
    {
        return [
            'activeLocale' => $this->getActiveActionsLocale() ?? null,
            'maxDepth' => -1,
            'maxVisibleDepth' => 20,
        ];
    }
}
