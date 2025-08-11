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
use SolutionForest\InspireCms\Filament\Resources\Templates\Actions\ExportContentTemplatesAction;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\TemplateInfoInfolist;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;

class TemplateInfo extends Widget implements GuardWidget, HasActions, HasForms, HasInfolists
{
    use GuardWidgetTrait;
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected string $view = 'inspirecms::filament.widgets.template-info';

    protected int | string | array $columnSpan = 'full';

    public static function getPermissionName(): string
    {
        return 'widgets_view-template-info';
    }

    public static function getPermissionDisplayName(): string
    {
        return strval(__('inspirecms::widgets.template_info.permission_display_name'));
    }

    public function infolist(Schema $schema): Schema
    {
        return TemplateInfoInfolist::configure($schema);
    }

    public function exportContentTemplatesAction(): Action
    {
        return ExportContentTemplatesAction::make();
    }
}
