<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;
use SolutionForest\InspireCms\Licensing\LicenseManager;

class CmsVersionInfo extends Widget implements GuardWidget, HasActions, HasForms
{
    use GuardWidgetTrait;
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'inspirecms::filament.widgets.cms-version-info';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public static function getPermissionName(): string
    {
        return 'widgets_view-cms-version-info';
    }

    public static function getPermissionDisplayName(): string
    {
        return strval(__('inspirecms::widgets.cms_version_info.permission_display_name'));
    }

    public function canUpgrade(): bool
    {
        return app(LicenseManager::class)->canUpgrade();
    }

    public function upgradeAction(): Action
    {
        return Action::make('upgrade')
            // ->label(__('inspirecms::widgets.cms_version_info.upgrade_action.label'))
            ->icon('heroicon-o-arrow-up-tray')
            ->url('http://localhost:8011/user/sites')
            ->openUrlInNewTab()
            ->color('primary');
    }
}
