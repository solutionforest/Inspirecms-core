<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Facades\FilamentIcon;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Events\Template\ChangeTheme;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Resources\Helpers\TemplateResourceHelper;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class ThemeInfo extends Widget implements GuardWidget, HasActions, HasForms, HasInfolists
{
    use GuardWidgetTrait;
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected static string $view = 'inspirecms::filament.widgets.theme-info';

    protected int | string | array $columnSpan = 'full';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $themeData = [];

    protected $listeners = [
        'refreshInfolists' => '$refresh',
    ];

    public function mount()
    {
        $this->fillInfolist();
    }

    public static function getPermissionName(): string
    {
        return 'widgets_view-theme-info';
    }

    public static function getPermissionDisplayName(): string
    {
        return strval(__('inspirecms::widgets.theme_info.permission_display_name'));
    }

    protected function fillInfolist()
    {
        $currentTheme = inspirecms_templates()->getCurrentTheme() ?? TemplateHelper::getDefaultTemplateTheme();

        $layoutPath = inspirecms_templates()->getThemeDefaultLayoutPath($currentTheme);

        $layoutRelativePath = str($layoutPath ? str_replace(base_path(), '', $layoutPath) : '')
            ->replace('\\', '/')
            ->trim('/')
            ->toString();

        $this->themeData = [
            'current_theme' => $currentTheme,
            'layout' => $layoutRelativePath,
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
            ->state($this->themeData)
            ->columns(1)
            ->schema([
                Infolists\Components\TextEntry::make('current_theme')
                    ->weight('bold')->color('primary')
                    ->label(__('inspirecms::inspirecms.current_xxx', ['name' => __('inspirecms::inspirecms.theme')]))
                    ->hintAction(
                        Infolists\Components\Actions\Action::make('changeTheme')
                            ->label(__('inspirecms::buttons.change_theme.label'))
                            ->icon(FilamentIcon::resolve('inspirecms::edit'))
                            ->link()
                            ->color('gray')
                            ->fillForm(fn ($component) => [
                                'theme' => $component->getState(),
                            ])
                            ->form([
                                TemplateResourceHelper::getThemeFormComponent()
                                    ->inlineLabel()
                                    ->required(),
                            ])
                            ->successNotificationTitle(__('inspirecms::buttons.change_theme.messages.success.title'))
                            ->failureNotificationTitle(__('inspirecms::buttons.change_theme.messages.failure.title'))
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

                Infolists\Components\TextEntry::make('layout')
                    ->label(__('inspirecms::inspirecms.layout'))
                    ->fontFamily(FontFamily::Mono)
                    ->size('xs')
                    ->placeholder(fn () => strval(__('inspirecms::inspirecms.n/a')))
                    ->extraAttributes(['class' => 'overflow-x-auto overflow-y-hidden']),
            ]);
    }

    private static function getThemeNameInputComponent(string $name = 'theme'): Forms\Components\Field | Forms\Components\Component
    {
        return Forms\Components\TextInput::make($name)
            ->label(__('inspirecms::resources/template.theme.label'))
            ->inlineLabel()
            ->required()
            ->live(true, 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state)));
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

                TemplateResourceHelper::getThemeFormComponent('source_theme')
                    ->label(__('inspirecms::resources/template.source_theme.label'))
                    ->inlineLabel()
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
}
