<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Widgets\Widget;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;

class CmsVersionInfo extends Widget implements GuardWidget
{
    use GuardWidgetTrait;
    use InteractsWithActions;
    protected static string $view = 'inspirecms::filament.widgets.cms-version-info';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public static function getPermissionName(): string
    {
        return 'widgets_view-cms-version-info';
    }

    public static function getPermissionDisplayName(): string
    {
        return strval(__('inspirecms::widgets.cms_info.permission_display_name'));
    }
}
