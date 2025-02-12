<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Concerns;

use Filament\Resources\Pages\PageRegistration;
use SolutionForest\InspireCms\Base\Filament\Pages\Concerns\HasExtraSubNavigation;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages\ListNavigationTable;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages\ListNavigationTree;

trait NavigationListPageTrait
{
    use HasExtraSubNavigation;

    protected function getExtraSubNavigationComponents(): array
    {
        $resource = static::getResource();

        return collect($resource::getPages())
            ->only(['index', 'table'])
            ->whereInstanceOf(PageRegistration::class)
            ->map(fn (PageRegistration $v) => $v->getPage())
            ->values()
            ->all();
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? str(class_basename(static::class))
            ->kebab()
            ->afterLast('-')
            ->title();
    }

    public function getBreadcrumb(): ?string
    {
        return static::$breadcrumb ?? str(class_basename(static::class))
            ->kebab()
            ->afterLast('-')
            ->title();
    }

    public function getView(): string
    {
        return 'inspirecms::filament.pages.list-navigation';
    }

    protected function getViewData(): array
    {
        return [
            'navigationPageType' => match (true) {
                $this instanceof ListNavigationTree => 'tree',
                $this instanceof ListNavigationTable => 'table',
                default => 'index',
            },
        ];
    }
}
