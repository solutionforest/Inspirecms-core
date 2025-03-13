<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Facades\FilamentIcon;
use Filament\Widgets\Widget;
use SolutionForest\InspireCms\Facades\Templates;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Resources\Helpers\TemplateResourceHelper;
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
    public ?array $data = [];

    protected $listeners = [
        'refreshInfolist' => '$refresh',
    ];

    public function mount()
    {
        $this->fillForm();
    }

    public static function getPermissionName(): string
    {
        return 'widgets_view-template-info';
    }

    public static function getPermissionDisplayName(): string
    {
        // todo: add translation
        return 'View Template Info';
    }

    protected function fillForm()
    {
        $currentTheme = Templates::getCurrentTheme() ?? TemplateHelper::getDefaultTemplateTheme();

        $this->data = [
            'current_theme' => $currentTheme,
            'is_theme_ready' => filled($currentTheme) && Templates::isThemeExists($currentTheme),
            'theme_layout' => Templates::getThemeDefaultLayoutPath($currentTheme),
            'exported_template_directory' => Templates::getExportedTemplateDir(),
        ];
    }

    public function refreshInfolist()
    {
        // Clear cached infolist
        reset($this->cachedInfolists);

        $this->fillForm();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $makeTextEntryForPath = function ($name): Infolists\Components\TextEntry {
            return Infolists\Components\TextEntry::make($name)
                ->fontFamily(FontFamily::Mono)
                ->copyable()
                ->size('xs')
                ->formatStateUsing(fn ($state) => filled($state) ? $state : 'N/A')
                ->extraAttributes(['class' => 'overflow-x-auto overflow-y-hidden']);
        };
        $isThemeReady = $this->data['is_theme_ready'];

        return $infolist
            ->state($this->data)
            ->columns(1)
            ->schema([
                Infolists\Components\TextEntry::make('current_theme')
                    ->weight('bold')->color('primary')
                    ->hintAction(
                        Infolists\Components\Actions\Action::make('editTheme')
                            ->icon(FilamentIcon::resolve('inspirecms::edit'))
                            ->iconButton()
                            ->color('gray')
                            ->fillForm(fn ($component) => ['value' => $component->getState()])
                            ->form(fn (Form $form) => $this->editThemeForm($form))
                            ->successNotificationTitle('Theme updated')
                            ->action(function (array $data, Infolists\Components\Actions\Action $action, $livewire) {

                                InspireCmsConfig::getKeyValueModelClass()::setKeyValue(
                                    TemplateHelper::getCurrentThemeKey(),
                                    $data['value']
                                );

                                Templates::resetCurrentTheme();
                                $this->refreshInfolist();

                                $action->success();
                            })
                    ),

                $makeTextEntryForPath('theme_layout')
                    ->hint($isThemeReady ? null : 'Theme layout component does not exist')
                    ->hintColor('primary')
                    ->hintAction(
                        Infolists\Components\Actions\Action::make('generateLayout')
                            ->visible(! $isThemeReady)
                            ->icon(FilamentIcon::resolve('inspirecms::export'))
                            ->iconButton()
                            ->color('gray')
                            ->requiresConfirmation()
                            ->modalSubmitActionLabel('Generate')
                            ->successNotificationTitle('Layout generated')
                            ->failureNotificationTitle('Failed to generate layout')
                            ->action(function (Infolists\Components\Actions\Action $action) {
                                try {

                                    Templates::ensureThemeLayoutComponentExists(Templates::getCurrentTheme());
                                    $this->refreshInfolist();

                                    $action->success();

                                } catch (\Throwable $th) {
                                    $action->failure();
                                }
                            })
                    ),

                $makeTextEntryForPath('exported_template_directory')
                    ->hintAction(
                        Infolists\Components\Actions\Action::make('exportTemplates')
                            ->icon(FilamentIcon::resolve('inspirecms::export'))
                            ->iconButton()
                            ->color('gray')
                            ->requiresConfirmation()
                            ->modalSubmitActionLabel('Export')
                            ->successNotification(
                                fn (Notification $notification) => $notification
                                    ->title(__('inspirecms::actions.export_templates.notification.success.title'))
                                    ->body(__('inspirecms::actions.export_templates.notification.success.body'))
                            )
                            ->failureNotification(
                                fn (Notification $notification) => $notification
                                    ->title(__('inspirecms::actions.export_templates.notification.failure.title'))
                            )
                            ->action(fn (Infolists\Components\Actions\Action $action) => $this->exportExistingTemplates() ? $action->success() : $action->failure())
                    ),
            ]);
    }

    public function editThemeForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('value')
                    ->inlineLabel()
                    ->label('Current theme')
                    ->datalist(collect(Templates::getAvailableThemes())->keys())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, $component) => $component->state(trim($state))),
            ]);
    }

    public function exportExistingTemplates()
    {

        $templates = InspireCmsConfig::getTemplateModelClass()::all();

        $failedTemplates = [];

        $themes = array_keys(TemplateResourceHelper::getThemeSelectOptions());

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

        return count($failedTemplates) <= 0;
    }
}
