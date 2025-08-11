<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Resources\Templates\Actions\CloneThemeAction;
use SolutionForest\InspireCms\Filament\Resources\Templates\Actions\CreateThemeAction;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\ThemeInfoInfolist;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;

class ThemeInfo extends Widget implements GuardWidget, HasActions, HasForms, HasInfolists
{
    use GuardWidgetTrait;
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected string $view = 'inspirecms::filament.widgets.theme-info';

    protected int | string | array $columnSpan = 'full';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $themeData = [];

    protected $listeners = [
        'refreshInfolists' => '$refresh',
    ];

    public static function getPermissionName(): string
    {
        return 'widgets_view-theme-info';
    }

    public static function getPermissionDisplayName(): string
    {
        return strval(__('inspirecms::widgets.theme_info.permission_display_name'));
    }

    public function infolist(Schema $schema): Schema
    {
        return ThemeInfoInfolist::configure($schema);
    }

    public function createThemeAction(): Action
    {
        return CreateThemeAction::make();
    }

    public function cloneThemeAction(): Action
    {
        return CloneThemeAction::make();
    }
}
