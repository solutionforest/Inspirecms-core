<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Actions\Action;
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
use Illuminate\Support\Str;
// use SolutionForest\InspireCms\Facades\Templates;
use SolutionForest\InspireCms\Events\Template\ChangeTheme;
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
    public ?array $themeData = [];

    /**
     * @var array<string, mixed> | null
     */
    public ?array $templateInfoData = [];

    protected $listeners = [
        'refreshInfolists' => '$refresh',
    ];

    public function mount()
    {
        $this->fillThemeInfo();
        $this->fillTemplateInfo();
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

    protected function fillThemeInfo()
    {
        $currentTheme = inspirecms_templates()->getCurrentTheme() ?? TemplateHelper::getDefaultTemplateTheme();

        $this->themeData = [
            'current_theme' => $currentTheme,
            'layout' => inspirecms_templates()->getThemeDefaultLayoutPath($currentTheme),
        ];
    }

    protected function fillTemplateInfo()
    {
        $this->templateInfoData = [
            'exported_content_template_directory' => TemplateHelper::getDirectoryForExportedTemplates(),
        ];
    }

    public function refreshInfolists()
    {
        // Clear cached infolist
        reset($this->cachedInfolists);

        $this->fillThemeInfo();
        $this->fillTemplateInfo();
    }

    public function themeInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state($this->themeData)
            ->columns(1)
            ->schema([
                Infolists\Components\TextEntry::make('current_theme')
                    ->weight('bold')->color('primary')
                    ->hintAction(
                        Infolists\Components\Actions\Action::make('changeTheme')
                            ->icon(FilamentIcon::resolve('inspirecms::edit'))
                            ->link()
                            ->color('gray')
                            ->fillForm(fn ($component) => [
                                'theme' => $component->getState(),
                            ])
                            ->form([
                                Forms\Components\Select::make('theme')
                                    ->inlineLabel()
                                    ->options(TemplateResourceHelper::getThemeSelectOptions())
                                    ->required(),
                            ])
                            ->successNotificationTitle('Theme updated')
                            ->action(function (array $data, $component, Infolists\Components\Actions\Action $action, $livewire) {

                                $oldTheme = $component->getState();
                                $newTheme = $data['theme'];

                                // Is different theme
                                if ($oldTheme !== $newTheme) {

                                    InspireCmsConfig::getKeyValueModelClass()::setKeyValue(
                                        TemplateHelper::getCurrentThemeKey(),
                                        $newTheme
                                    );

                                    event(new ChangeTheme($oldTheme, $newTheme));
    
                                    inspirecms_templates()->resetCurrentTheme();
                                    $this->refreshInfolists();
    
                                    $action->success();

                                } 
                            })
                    ),

                    static::makeTextEntryForPath('layout'),
            ]);
    }

    public function templateInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state($this->templateInfoData)
            ->columns(1)
            ->schema([
                static::makeTextEntryForPath('exported_content_template_directory')
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

    public function createThemeAction(): Action
    {
        return Action::make('createTheme')
            ->icon(FilamentIcon::resolve('inspirecms::add'))
            ->label(__('inspirecms::buttons.create_theme.label'))
            ->successNotificationTitle(__('inspirecms::buttons.create_theme.messages.success.title'))
            ->failureNotificationTitle(__('inspirecms::buttons.create_theme.messages.failure.title'))
            ->form([
                static::getThemeNameInputComponent('theme'),
            ])
            ->action(function (array $data, Action $action) {
                $theme = $data['theme'];

                if (inspirecms_templates()->isThemeExists($theme)) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.theme_already_exists')));
                    $action->failure();
                    return;
                }

                try {
                    inspirecms_templates()->createTheme($theme);

                    $action->success();

                } catch (\Throwable $th) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.something_went_wrong')));
                    $action->failure();
                }
            });
    }
    
    public function cloneThemeAction(): Action
    {
        return Action::make('cloneTheme')
            ->icon(FilamentIcon::resolve('inspirecms::clone'))
            ->modalIcon(FilamentIcon::resolve('inspirecms::clone'))
            ->color('gray')
            ->label(__('inspirecms::buttons.clone_theme.label'))
            ->successNotificationTitle(__('inspirecms::buttons.clone_theme.messages.success.title'))
            ->failureNotificationTitle(__('inspirecms::buttons.clone_theme.messages.failure.title'))
            ->form([
                static::getThemeNameInputComponent('theme'),
                Forms\Components\Select::make('source_theme')
                    ->inlineLabel()
                    ->options(TemplateResourceHelper::getThemeSelectOptions())
                    ->required(),
            ])
            ->action(function (array $data, Action $action) {
                $sourceTheme = $data['source_theme'];
                $newTheme = $data['theme'];

                if ($sourceTheme === $newTheme) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.same_theme_name_already_exists')));
                    $action->failure();
                    return;
                }

                if (inspirecms_templates()->isThemeExists($newTheme)) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.theme_already_exists')));
                    $action->failure();
                    return;
                }

                try {
                    
                    inspirecms_templates()->cloneTheme($sourceTheme, $newTheme);

                    $action->success();

                } catch (\Throwable $th) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.something_went_wrong')));
                    $action->failure();
                }

            });
    }

    public function exportContentTemplatesAction(): Action
    {
        return Action::make('exportContentTemplates')
            ->icon(FilamentIcon::resolve('inspirecms::export'))
            ->modalIcon(FilamentIcon::resolve('inspirecms::export'))
            ->color('gray')
            ->successNotificationTitle(__('inspirecms::messages.success'))
            ->failureNotificationTitle(__('inspirecms::messages.failure'))
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

    private static function getThemeNameInputComponent(string $name = 'theme'): Forms\Components\Field | Forms\Components\Component
    {
        return Forms\Components\TextInput::make($name)
            ->inlineLabel()
            ->required()
            ->live(true, 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state)));
    }

    private static function makeTextEntryForPath(string $name): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make($name)
            ->fontFamily(FontFamily::Mono)
            ->copyable()
            ->size('xs')
            ->formatStateUsing(fn ($state) => filled($state) ? $state : __('inspirecms::inspirecms::messages.n/a'))
            ->extraAttributes(['class' => 'overflow-x-auto overflow-y-hidden']);
    }
}
