<?php

namespace SolutionForest\InspireCms;

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
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Pboivin\FilamentPeek\FilamentPeekPlugin;
use SolutionForest\FilamentFieldGroup\FilamentFieldGroupPlugin;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\Filament\Pages;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Widgets\TreeNavigation;
use SolutionForest\InspireCms\Filament\Widgets;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\UrlHelper;
use SolutionForest\InspireCms\Http\Middleware as CmsMiddleware;
use SolutionForest\InspireCms\Livewire\ListImportNExport;
use SolutionForest\InspireCms\Support\Base\Filament\ThemeConfig;
use SolutionForest\InspireCms\View\Components as ViewComponents;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id(InspireCmsConfig::getPanelId());

        return $this->configureCmsPanel($panel);
    }

    protected function configureCmsPanel(Panel $panel)
    {
        $panel = $panel
            ->path(InspireCmsConfig::get('admin.path', 'cms'))
            ->default()
            ->brandName(InspireCmsConfig::get('admin.brand.name', 'InspireCMS'))
            ->brandLogo(InspireCmsConfig::get('admin.brand.logo', fn () => view('inspirecms::logo')))
            ->favicon(InspireCmsConfig::get('admin.brand.favicon', fn () => asset('images/favicon.png')))
            ->authGuard(AuthHelper::guardName())
            ->login(Pages\Auth\Login::class)
            ->registration(Pages\Auth\Register::class)
            ->emailVerification()->emailVerificationRoutePrefix('inspirecms/verification')->emailVerificationRouteSlug('verify-user')
            ->profile(Pages\Auth\EditProfile::class)
            ->homeUrl(fn () => UrlHelper::attemptToGetUrlFromPanel(InspireCmsConfig::getFilamentPage('dashboard', Pages\Dashboard::class)))
            ->theme('inspirecms')
            ->font(ThemeConfig::fontFamily())
            ->colors(ThemeConfig::colors())
            ->maxContentWidth('full');

        if (AuthHelper::enablePasswordReset()) {
            $panel = $panel
                ->passwordReset()
                ->authPasswordBroker(AuthHelper::passwordBrokerName());
        }

        $panel = $panel
            ->resources(InspireCmsConfig::getFilamentResources())
            ->pages(array_merge(
                array_values(InspireCmsConfig::getFilamentPages()),
                collect(inspirecms()->getSections())->map(fn (ClusterSection $section) => $section->getFqcn())->all()
            ))
            ->widgets([
                Widgets\CmsInfoWidget::class,
                Widgets\PageActivity::class,
                Widgets\UserActivity::class,
                Widgets\AlertOverview::class,
                Widgets\TemplateInfo::class,
                TreeNavigation::class,
            ]);
        // Discover resources, pages, clusters, and widgets in the specified directories
        $panel = $panel->discoverResources(in: app_path('Cms/Resources'), for: 'App\\Cms\\Resources')
            ->discoverPages(in: app_path('Cms/Pages'), for: 'App\\Cms\\Pages')
            ->discoverClusters(in: app_path('Cms/Clusters'), for: 'App\\Cms\\Clusters')
            ->discoverWidgets(in: app_path('Cms/Widgets'), for: 'App\\Cms\\Widgets');

        $middleware = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            CmsMiddleware\CmsAuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
            CmsMiddleware\SetUpPoweredBy::class,
        ];
        $authMiddleware = [
            CmsMiddleware\LicenseCheck::class,
            CmsMiddleware\CmsAuthenticate::class,
            CmsMiddleware\UserPreference::class,
        ];

        $panel = $panel
            ->middleware($middleware)
            ->authMiddleware($authMiddleware)
            ->bootUsing(function () {

                $skipSuperAdminCheck = AuthHelper::skipSuperAdminCheck();
                if ($skipSuperAdminCheck == 'before') {
                    Gate::before(function ($user, $ability) {
                        if (has_super_admin_role($user)) {
                            return true;
                        }
                    });
                } elseif ($skipSuperAdminCheck == 'after') {
                    Gate::after(function ($user, $ability) {
                        if (has_super_admin_role($user)) {
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
            ->fieldTypeConfigs(InspireCmsConfig::get('custom_fields.extra_config'), false);

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
        $position = InspireCmsConfig::get('admin.navigation_position', 'top');
        return $panel
            ->topNavigation($position == 'top')
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
                // \SolutionForest\InspireCms\Filament\Navigation\MenuItem::make()
                //     ->label('Reset Tour Guide')
                //     ->icon('heroicon-s-arrow-path')
                //     ->button()
                //     ->extraAttributes([
                //         'class' => 'tour-guide-reset-btn',
                //         'aria-label' => 'Reset Tour Guide',
                //     ], true),
                \SolutionForest\InspireCms\Filament\Navigation\MenuItem::make()
                    ->label(fn () => __('inspirecms::inspirecms.version') . ': ' . InspireCms::version())
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
        if (InspireCmsConfig::get('admin.database_notification.enabled')) {
            $panel
                ->databaseNotifications()
                ->databaseNotificationsPolling(
                    InspireCmsConfig::get('admin.database_notification.polling_interval', '30s')
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
            ListImportNExport::class,
        ]);
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
