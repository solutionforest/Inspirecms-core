<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Facades\FilamentIcon;
use Filament\Widgets\Widget;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateInfo extends Widget implements GuardWidget, HasActions, HasForms, HasInfolists
{
    use GuardWidgetTrait;
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected static string $view = 'inspirecms::filament.widgets.template-info';

    protected int | string | array $columnSpan = 'full';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $templateInfoData = [];

    protected $listeners = [
        'refreshInfolists' => '$refresh',
    ];

    public function mount()
    {
        $this->fillInfolist();
    }

    public static function getPermissionName(): string
    {
        return 'widgets_view-template-info';
    }

    public static function getPermissionDisplayName(): string
    {
        return strval(__('inspirecms::widgets.template_info.permission_display_name'));
    }

    protected function fillInfolist()
    {
        $fullPath = TemplateHelper::getDirectoryForExportedTemplates();

        $relativePath = str($fullPath ? str_replace(base_path(), '', $fullPath) : '')
            ->replace('\\', '/')
            ->trim('/')
            ->toString();

        $this->templateInfoData = [
            'exported_content_template_directory' => $relativePath,
        ];
    }

    public function refreshInfolists()
    {
        // Clear cached infolist
        reset($this->cachedInfolists);

        $this->fillInfolist();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state($this->templateInfoData)
            ->columns(1)
            ->schema([
                Infolists\Components\TextEntry::make('exported_content_template_directory')
                    ->label(__('inspirecms::inspirecms.exported_content_template_directory'))
                    ->fontFamily(FontFamily::Mono)
                    ->size('xs')
                    ->placeholder(fn () => strval(__('inspirecms::inspirecms.n/a')))
                    ->extraAttributes(['class' => 'overflow-x-auto overflow-y-hidden']),
            ]);
    }

    public function exportContentTemplatesAction(): Action
    {
        return Action::make('exportContentTemplates')
            ->label(__('inspirecms::buttons.export_content_templates.label'))
            ->icon(FilamentIcon::resolve('inspirecms::export'))
            ->modalIcon(FilamentIcon::resolve('inspirecms::export'))
            ->color('gray')
            ->successNotificationTitle(__('inspirecms::buttons.export_content_templates.messages.success.title'))
            ->failureNotificationTitle(__('inspirecms::buttons.export_content_templates.messages.failure.title'))
            ->action(function (Action $action) {

                $templates = InspireCmsConfig::getTemplateModelClass()::all();

                $failedTemplates = [];

                $themes = inspirecms_templates()->getAvailableThemes();

                foreach ($themes as $theme) {

                    foreach ($templates as $template) {

                        try {

                            inspirecms_templates()->exportTemplate($template, $theme);

                        } catch (\Throwable $th) {

                            $failedTemplates[$theme][$template->getKey()] = $th->getMessage();

                            logger()->warning(
                                'Failed to export template',
                                [
                                    'template' => $template->getKey(),
                                    'theme' => $theme,
                                    'error' => $th->getMessage(),
                                ]
                            );
                        }
                    }
                }

                if (count($failedTemplates) <= 0) {
                    $action->success();
                } else {
                    $action->failure();
                }
            });
    }
}
