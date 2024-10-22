<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseManagePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Widgets;

class ListNavigation extends BaseManagePage
{
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

    protected function getHeaderWidgets(): array
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

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.navigation', NavigationResource::class);
    }
}
