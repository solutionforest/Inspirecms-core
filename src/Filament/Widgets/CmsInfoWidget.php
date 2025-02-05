<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Widgets\Widget;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;

class CmsInfoWidget extends Widget implements GuardWidget
{
    use GuardWidgetTrait;

    protected static string $view = 'inspirecms::filament.widgets.cms-info';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public static function getPermissionName(): string
    {
        return 'widgets_view-cms-info';
    }

    public static function getPermissionDisplayName(): string
    {
        // todo: add translation
        return 'View CMS Info';
    }

    public function getDocumentUrl(): string
    {
        return 'https://docs.inspirecms.io';
    }

    public function getNewsUrl(): string
    {
        return 'https://inspirecms.io/news';
    }

    public function getLightScreenShotUrl(): string
    {
        return 'https://laravel.com/assets/img/welcome/docs-light.svg';
    }

    public function getDarkScreenShotUrl(): string
    {
        return 'https://laravel.com/assets/img/welcome/docs-dark.svg';
    }
}
