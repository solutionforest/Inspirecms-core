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
        return strval(__('inspirecms::widgets.cms_info.permission_display_name'));
    }

    public function getDocumentUrl(): string
    {
        return 'https://inspirecms.net/docs';
    }

    public function getNewsUrl(): string
    {
        return 'https://inspirecms.net/blog';
    }
}
