<?php

namespace SolutionForest\InspireCms\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use SolutionForest\InspireCms\Filament\Widgets\PageActivity;

class Dashboard extends Page
{
    protected static string $routePath = '/';

    protected static ?int $navigationSort = -2;

    protected static bool $shouldRegisterNavigation = false;

    public static string $view = 'inspirecms::filament.pages.dashboard';

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::inspirecms.dashboard');
    }

    public function getTitle(): string | Htmlable
    {
        return __('inspirecms::inspirecms.dashboard');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PageActivity::class,
        ];
    }
}
