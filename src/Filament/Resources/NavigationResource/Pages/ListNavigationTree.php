<?php

namespace SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages;

use Filament\Actions\CreateAction;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Concerns\NavigationListPageTrait;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListNavigationTree extends BaseListRecords
{
    use NavigationListPageTrait;
    use Translatable;

    protected string $view = 'inspirecms::filament.resources.navigation.pages.tree';

    protected $queryString = [
        'activeLocale',
    ];

    public function getActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
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
