<?php

namespace SolutionForest\InspireCms;

use Closure;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Pboivin\FilamentPeek\FilamentPeekPlugin;
use SolutionForest\FilamentFieldGroup\FilamentFieldGroupPlugin;
use SolutionForest\InspireCms\Filament\Pages;
use SolutionForest\InspireCms\Filament\Widgets;
use SolutionForest\InspireCms\Http\Middlewares\UserPreference;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id(config('insiprecms.filament.panel_id', 'cms'))
            ->path(config('insiprecms.filament.path', 'cms'))
            ->default()
            ->brandName('InspireCms')->brandLogo(fn () => view('inspirecms::logo'))
            ->authGuard(InspireCmsConfig::getGuardName())
            ->login(Pages\Auth\Login::class)
            ->profile(Pages\Auth\EditProfile::class)
            ->routes($this->getExtraRoutes())
            ->homeUrl(fn () => Pages\Dashboard::getUrl())
            ->theme('inspirecms')
            ->font('DM Sans')
            ->colors([
                'danger' => Color::hex('#f44336'),
                'gray' => Color::hex('#5e5e5e'),
                'info' => Color::hex('#88B0BA'),
                'primary' => Color::hex('#B5834A'),
                'secondary' => Color::hex('#d2b492'),
                'success' => Color::hex('#76ae51'),
                'warning' => Color::hex('#f39e19'),

                'zinc' => Color::Zinc,
            ])
            ->maxContentWidth('full')
            ->plugins([
                FilamentFieldGroupPlugin::make()
                    ->enablePlugin()
                    ->overrideResources([])
                    ->fieldTypeConfigs([
                        \SolutionForest\InspireCms\Fields\Configs\Repeater::class,

                        \SolutionForest\InspireCms\Fields\Configs\RichEditor::class,
                        \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor::class,

                        \SolutionForest\InspireCms\Fields\Configs\Translate::class,

                        \SolutionForest\InspireCms\Fields\Configs\ContentPicker::class,
                        \SolutionForest\InspireCms\Fields\Configs\MediaPicker::class,
                    ], false),
                FilamentPeekPlugin::make(),
                \Filament\SpatieLaravelTranslatablePlugin::make(),
            ])
            ->resources(config('inspirecms.filament.resources'))
            ->pages([
                ...array_values(config('inspirecms.filament.pages')),
                ...\SolutionForest\InspireCms\Facades\InspireCms::getSections()
                    ->map(fn (\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection $section) => $section->getFqcn())
                    ->all(),
            ])
            ->widgets([
                Widgets\PageActivity::class,
                Widgets\AlertOverview::class,
            ])
            ->discoverResources(in: app_path('Cms/Resources'), for: 'App\\Cms\\Resources')
            ->discoverPages(in: app_path('Cms/Pages'), for: 'App\\Cms\\Pages')
            ->discoverClusters(in: app_path('Cms/Clusters'), for: 'App\\Cms\\Clusters')
            ->discoverWidgets(in: app_path('Cms/Widgets'), for: 'App\\Cms\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                UserPreference::class,
            ])
            ->bootUsing(function () {

                // Gate for super admin
                Gate::before(function ($user, $ability) {
                    if ($user && is_inspirecms_user($user) && $user->isSuperAdmin()) {
                        return true;
                    }
                });

            });

        $this->configureNavigation($panel);
        $this->configureNotification($panel);
        $this->configureFilamentActions($panel);
        $this->configureTourGuideElements($panel);
        $this->registerLivewireComponents($panel);

        return $panel;
    }

    protected function configureNavigation(Panel $panel): Panel
    {
        return $panel
            ->topNavigation()
            ->navigationGroups([
                'content' => NavigationGroup::make(fn () => __('inspirecms::inspirecms.content')),
                'media' => NavigationGroup::make(fn () => __('inspirecms::inspirecms.media')),
                'settings' => NavigationGroup::make(fn () => __('inspirecms::inspirecms.settings')),
                'users' => NavigationGroup::make(fn () => __('inspirecms::inspirecms.users')),
            ]);
    }

    protected function configureTourGuideElements(Panel $panel): Panel
    {
        return $panel
            ->userMenuItems([
                \SolutionForest\InspireCms\Filament\Navigation\MenuItem::make()
                    ->label('Reset Tour Guide')
                    ->icon('heroicon-s-arrow-path')
                    ->button()
                    ->extraAttributes([
                        'class' => 'tour-guide-reset-btn',
                        'aria-label' => 'Reset Tour Guide',
                    ], true),
                \SolutionForest\InspireCms\Filament\Navigation\MenuItem::make()
                    ->label('Version: ' . InspireCmsConfig::getVersion())
                    ->icon('heroicon-s-information-circle')
                    ->url('#')
                    ->extraAttributes([
                        'class' => 'text-xs',
                        'aria-label' => 'Version',
                    ], true),
            ])
            ->bootUsing(function () {
                $this->app->singleton(\Filament\Navigation\NavigationItem::class, \SolutionForest\InspireCms\Filament\Navigation\NavigationItem::class);
                \Filament\Navigation\NavigationItem::macro('section', function ($section) {
                    $cast = $this->cloneAsCustom();
                    $cast->section = $section;

                    return $cast;
                });
                \Filament\Navigation\NavigationItem::macro('itemKey', function ($itemKey) {
                    $cast = $this->cloneAsCustom();
                    $cast->itemKey = $itemKey;

                    return $cast;
                });
                \Filament\Navigation\NavigationItem::macro('cloneAsCustom', function ($fqcn = \SolutionForest\InspireCms\Filament\Navigation\NavigationItem::class) {
                    $tmp = new $fqcn;
                    $tmp->label = $this->label;
                    $tmp->group = $this->group;
                    $tmp->parentItem = $this->parentItem;
                    $tmp->isActive = $this->isActive;
                    $tmp->icon = $this->icon;
                    $tmp->activeIcon = $this->activeIcon;
                    $tmp->badge = $this->badge;
                    $tmp->badgeColor = $this->badgeColor;
                    $tmp->badgeTooltip = $this->badgeTooltip;
                    $tmp->shouldOpenUrlInNewTab = $this->shouldOpenUrlInNewTab;
                    $tmp->sort = $this->sort;
                    $tmp->url = $this->url;
                    $tmp->isHidden = $this->isHidden;
                    $tmp->isVisible = $this->isVisible;
                    $tmp->childItems = $this->childItems;

                    return $tmp;
                });

                Blade::component('filament-panels::topbar', \SolutionForest\InspireCms\View\Components\TopBar::class);
                Blade::component('filament-panels::sidebar', \SolutionForest\InspireCms\View\Components\Sidebar::class);
                Blade::component('filament-panels::sidebar.group', \SolutionForest\InspireCms\View\Components\SidebarGroup::class);

                Blade::component('filament-panels::user-menu', \SolutionForest\InspireCms\View\Components\UserMenu::class);

                Blade::component('filament-panels::resources.relation-managers', \SolutionForest\InspireCms\View\Components\Resources\RelationManagers::class);

                \Filament\Actions\Action::configureUsing(function (\Filament\Actions\Action $action) {
                    $action->extraAttributes([
                        'data-action-name' => $action->getName(),
                    ], true);
                });
            });
    }

    protected function configureNotification(Panel $panel): Panel
    {
        if (InspireCmsConfig::get('filament.database_notification.enabled')) {
            $panel
                ->databaseNotifications()
                ->databaseNotificationsPolling(
                    InspireCmsConfig::get('filament.database_notification.polling_interval', '30s')
                );
        }

        return $panel;
    }

    protected function configureFilamentActions(Panel $panel): Panel
    {
        return $panel->bootUsing(function () {
            \Filament\Actions\Action::configureUsing(function (\Filament\Actions\Action $action) {
                $action->modalFooterActionsAlignment(Alignment::End);
            });
            \Filament\Tables\Actions\Action::configureUsing(function (\Filament\Tables\Actions\Action $action) {
                $action->modalFooterActionsAlignment(Alignment::End);
            });
            \Filament\Forms\Components\Actions\Action::configureUsing(function (\Filament\Forms\Components\Actions\Action $action) {
                $action->modalFooterActionsAlignment(Alignment::End);
            });
            \Filament\Actions\EditAction::configureUsing(function (\Filament\Actions\EditAction $action) {
                $action->icon(function (\Filament\Actions\EditAction $action) {
                    if ($action->isIconButton()) {
                        return FilamentIcon::resolve('actions::edit-action.grouped') ?? 'heroicon-m-pencil-square';
                    }
                });
            });
            \Filament\Actions\ViewAction::configureUsing(function (\Filament\Actions\ViewAction $action) {
                $action->icon(function (\Filament\Actions\ViewAction $action) {
                    if ($action->isIconButton()) {
                        return FilamentIcon::resolve('actions::view-action.grouped') ?? 'heroicon-m-eye';
                    }
                });
            });
            \Filament\Actions\DeleteAction::configureUsing(function (\Filament\Actions\DeleteAction $action) {
                $action->icon(function (\Filament\Actions\DeleteAction $action) {
                    if ($action->isIconButton()) {
                        return FilamentIcon::resolve('actions::delete-action.grouped') ?? 'heroicon-m-trash';
                    }
                });
            });
            \Filament\Actions\ForceDeleteAction::configureUsing(function (\Filament\Actions\ForceDeleteAction $action) {
                $action->icon(function (\Filament\Actions\ForceDeleteAction $action) {
                    if ($action->isIconButton()) {
                        return FilamentIcon::resolve('actions::force-delete-action.modal') ?? 'heroicon-o-trash';
                    }
                });
            });
            \Filament\Actions\RestoreAction::configureUsing(function (\Filament\Actions\RestoreAction $action) {
                $action->icon(function (\Filament\Actions\RestoreAction $action) {
                    if ($action->isIconButton()) {
                        return FilamentIcon::resolve('actions::restore-action.grouped') ?? 'heroicon-m-arrow-uturn-left';
                    }
                });
            });
            \Pboivin\FilamentPeek\Pages\Actions\PreviewAction::configureUsing(function (\Pboivin\FilamentPeek\Pages\Actions\PreviewAction $action) {
                $action->icon('heroicon-o-eye');
            });
            \Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction::configureUsing(function (\Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction $action) {
                $action->icon('heroicon-o-eye');
            });
        });
    }

    protected function registerLivewireComponents(Panel $panel): Panel
    {
        return $panel->livewireComponents([
            Pages\Auth\Install::class,
        ]);
    }

    protected function getExtraRoutes(): ?Closure
    {
        return function (Panel $panel) {

            \Illuminate\Support\Facades\Route::get(Pages\Auth\Install::getRouteSlug(), Pages\Auth\Install::class)
                ->name('install');
        };
    }
}
