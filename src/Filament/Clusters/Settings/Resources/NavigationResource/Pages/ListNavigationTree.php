<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use Livewire\Attributes\On;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseManagePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Concerns\NavigationListPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Widgets;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListNavigationTree extends BaseManagePage
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

    protected function getWidgets(): array
    {
        $commonNavWidgetData = [
            'resource' => static::getResource(),
            'activeLocale' => $this->getActiveActionsLocale(),
            'translatableContentDriver' => $this->getFilamentTranslatableContentDriver(),
        ];

        $widgets = collect(static::getResource()::getWidgets())
            ->filter(fn ($fqcn) => is_a($fqcn, Widgets\BaseTreeNavigation::class, true))
            ->values()
            ->map(fn ($fqcn) => $fqcn::make($commonNavWidgetData))
            ->all();

        return $widgets;
    }

    #[On('refreshAllTree')]
    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }

    public function getWidgetsColumns(): int | string | array
    {
        return 1;
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }
}
