<?php

namespace SolutionForest\InspireCms\Filament\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use SolutionForest\InspireCms\Filament\Widgets\CmsInfoWidget;
use SolutionForest\InspireCms\Filament\Widgets\PageActivity;
use SolutionForest\InspireCms\Filament\Widgets\UserActivity;
use SolutionForest\InspireCms\InspireCmsConfig;

class Dashboard extends Page
{
    protected static string $routePath = '/';

    protected static ?int $navigationSort = -999;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-s-home';

    protected Width | string | null $maxContentWidth = 'screen-xl';

    protected static bool $shouldRegisterNavigation = true;

    public string $view = 'inspirecms::filament.pages.dashboard';

    public static function getRoutePath(Panel $panel): string
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
            CmsInfoWidget::class,
            PageActivity::class,
            UserActivity::class,
            ...InspireCmsConfig::get('admin.extra_widgets', []),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
