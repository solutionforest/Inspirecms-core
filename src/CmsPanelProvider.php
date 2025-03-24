<?php

namespace SolutionForest\InspireCms;

use Closure;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Pboivin\FilamentPeek\FilamentPeekPlugin;
use SolutionForest\FilamentFieldGroup\FilamentFieldGroupPlugin;
use SolutionForest\InspireCms\Filament\Pages;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Widgets\TreeNavigation;
use SolutionForest\InspireCms\Filament\Widgets;
use SolutionForest\InspireCms\Http\Middleware\CmsAuthenticate;
use SolutionForest\InspireCms\Http\Middleware\CmsAuthenticateSession;
use SolutionForest\InspireCms\Http\Middleware\LicenseCheck;
use SolutionForest\InspireCms\Http\Middleware\UserPreference;
use SolutionForest\InspireCms\Livewire\ListImportNExport;
use SolutionForest\InspireCms\Support\Base\Filament\ThemeConfig;
use SolutionForest\InspireCms\View\Components as ViewComponents;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id(InspireCmsConfig::get('filament.panel_id', 'cms'))
            ->path(InspireCmsConfig::get('filament.path', 'cms'))
            ->default()
            ->brandName('InspireCms')->brandLogo(fn () => view('inspirecms::logo'))
            ->authGuard(InspireCmsConfig::getGuardName())
            ->login(Pages\Auth\Login::class)
            ->registration(Pages\Auth\Install::class)
            ->profile(Pages\Auth\EditProfile::class)
            ->routes($this->getExtraRoutes())
            ->homeUrl(fn () => Pages\Dashboard::getUrl())
            ->theme('inspirecms')
            ->font(ThemeConfig::fontFamily())
            ->colors(ThemeConfig::colors())
            ->maxContentWidth('full')
            ->resources(InspireCmsConfig::get('filament.resources'))
            ->pages([
                ...array_values(InspireCmsConfig::get('filament.pages')),
                ...\SolutionForest\InspireCms\Facades\InspireCms::getSections()
                    ->map(fn (\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection $section) => $section->getFqcn())
                    ->all(),
            ])
            ->widgets([
                Widgets\CmsInfoWidget::class,
                Widgets\PageActivity::class,
                Widgets\AlertOverview::class,
                Widgets\TemplateInfo::class,
                TreeNavigation::class,
            ])
            ->discoverResources(in: app_path('Cms/Resources'), for: 'App\\Cms\\Resources')
            ->discoverPages(in: app_path('Cms/Pages'), for: 'App\\Cms\\Pages')
            ->discoverClusters(in: app_path('Cms/Clusters'), for: 'App\\Cms\\Clusters')
            ->discoverWidgets(in: app_path('Cms/Widgets'), for: 'App\\Cms\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                CmsAuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                LicenseCheck::class,
                CmsAuthenticate::class,
                UserPreference::class,
            ])
            ->bootUsing(function () {

                $skipSuperAdminCheck = InspireCmsConfig::get('auth.skip_super_admin_check');
                if ($skipSuperAdminCheck == 'before') {
                    Gate::before(function ($user, $ability) {
                        if ($user && is_inspirecms_user($user) && $user->isSuperAdmin()) {
                            return true;
                        }
                    });
                } elseif ($skipSuperAdminCheck == 'after') {
                    Gate::after(function ($user, $ability) {
                        if ($user && is_inspirecms_user($user) && $user->isSuperAdmin()) {
                            return true;
                        }
                    });
                }
            });

        $this->configurePlugins($panel);
        $this->configureNavigation($panel);
        $this->configureNotification($panel);
        $this->configureFilamentActions($panel);
        $this->configureTourGuideElements($panel);
        $this->registerLivewireComponents($panel);

        return $panel;
    }

    protected function configurePlugins(Panel $panel): Panel
    {
        $plugins[] = FilamentPeekPlugin::make();
        $plugins[] = FilamentFieldGroupPlugin::make()
            ->enablePlugin()
            ->overrideResources([])
            ->fieldTypeConfigs([
                \SolutionForest\InspireCms\Fields\Configs\Repeater::class,
                \SolutionForest\InspireCms\Fields\Configs\Tags::class,

                \SolutionForest\InspireCms\Fields\Configs\RichEditor::class,
                \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor::class,

                \SolutionForest\InspireCms\Fields\Configs\ContentPicker::class,
                \SolutionForest\InspireCms\Fields\Configs\MediaPicker::class,
            ], false);

        $translatablePlugin = \Filament\SpatieLaravelTranslatablePlugin::make();
        $translatablePlugin->getLocaleLabelUsing(function ($locale, $displayLocale) {

            $lang = data_get(\SolutionForest\InspireCms\Facades\InspireCms::getAllAvailableLanguages(), $locale);

            if (! $lang) {
                return null;
            }

            $label = $lang->getLabel($displayLocale);

            if ($lang->isDefault == true) {
                $label .= ' [' . __('inspirecms::inspirecms.default') . ']';
            }

            return $label;

        });

        $plugins[] = $translatablePlugin;

        return $panel->plugins($plugins);
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
        // todo: add translations
        return $panel
            ->userMenuItems([
                // \SolutionForest\InspireCms\Filament\Navigation\MenuItem::make()
                //     ->label('Reset Tour Guide')
                //     ->icon('heroicon-s-arrow-path')
                //     ->button()
                //     ->extraAttributes([
                //         'class' => 'tour-guide-reset-btn',
                //         'aria-label' => 'Reset Tour Guide',
                //     ], true),
                \SolutionForest\InspireCms\Filament\Navigation\MenuItem::make()
                    ->label('Version: ' . InspireCms::version())
                    ->icon(fn () => FilamentIcon::resolve('inspirecms::info'))
                    ->url('#')
                    ->extraAttributes([
                        'class' => 'cursor-default',
                        'aria-label' => 'Version',
                    ], true),
            ])
            ->bootUsing(function () {
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

                $this->replaceViewComponents();

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
                    return $action->isIconButton() 
                        ? FilamentIcon::resolve('inspirecms::edit')
                        : null;
                });
            });
            \Filament\Actions\ViewAction::configureUsing(function (\Filament\Actions\ViewAction $action) {
                $action->icon(function (\Filament\Actions\ViewAction $action) {
                    return $action->isIconButton() 
                        ? FilamentIcon::resolve('inspirecms::visible') 
                        : null;
                });
            });
            \Filament\Actions\DeleteAction::configureUsing(function (\Filament\Actions\DeleteAction $action) {
                $action->icon(function (\Filament\Actions\DeleteAction $action) {
                    return $action->isIconButton() 
                        ? FilamentIcon::resolve('inspirecms::delete') 
                        : null;
                });
            });
            \Filament\Actions\ForceDeleteAction::configureUsing(function (\Filament\Actions\ForceDeleteAction $action) {
                $action->icon(function (\Filament\Actions\ForceDeleteAction $action) {
                    return $action->isIconButton() 
                        ? FilamentIcon::resolve('inspirecms::delete') 
                        : null;
                });
            });
            \Filament\Actions\RestoreAction::configureUsing(function (\Filament\Actions\RestoreAction $action) {
                $action->icon(function (\Filament\Actions\RestoreAction $action) {
                    return $action->isIconButton() 
                        ? FilamentIcon::resolve('inspirecms::restore') 
                        : null;
                });
            });
            \Filament\Tables\Actions\ReplicateAction::configureUsing(function (\Filament\Tables\Actions\ReplicateAction $action) {
                $action
                    ->color('gray')
                    ->modalIcon(FilamentIcon::resolve('inspirecms::clone'));
            });
            \Pboivin\FilamentPeek\Pages\Actions\PreviewAction::configureUsing(function (\Pboivin\FilamentPeek\Pages\Actions\PreviewAction $action) {
                $action->icon(FilamentIcon::resolve('inspirecms::preview'));
            });
            \Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction::configureUsing(function (\Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction $action) {
                $action->icon(FilamentIcon::resolve('inspirecms::preview'));
            });
        });
    }

    protected function registerLivewireComponents(Panel $panel): Panel
    {
        return $panel->livewireComponents([
            Pages\Auth\Install::class,
            ListImportNExport::class,
        ]);
    }

    protected function getExtraRoutes(): ?Closure
    {
        return function (Panel $panel) {

            Route::get(Pages\Auth\Install::getRouteSlug(), Pages\Auth\Install::class)
                ->name('install');

            Route::name('import.')->prefix('import')->group(function () {
                Route::get('sample', [\SolutionForest\InspireCms\Http\Controllers\ImportController::class, 'sample'])->name('sample');
            });
        };
    }

    protected function replaceViewComponents()
    {
        Blade::component('filament-panels::topbar', ViewComponents\Filament\TopBar::class);
        Blade::component('filament-panels::sidebar', ViewComponents\Filament\Sidebar::class);
        Blade::component('filament-panels::sidebar.group', ViewComponents\Filament\SidebarGroup::class);

        Blade::component('filament-panels::user-menu', ViewComponents\Filament\UserMenu::class);

        Blade::component('filament-panels::resources.relation-managers', ViewComponents\Filament\Resources\RelationManagers::class);

    }
}
