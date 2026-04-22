<?php

namespace SolutionForest\InspireCms;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Actions\Action as FormComponentsAction;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Actions\Action as TablesAction;
use Filament\Tables\Actions\ReplicateAction as TablesReplicateAction;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Livewire\Livewire;
use Pboivin\FilamentPeek\FilamentPeekPlugin;
use Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use SolutionForest\FilamentFieldGroup\FilamentFieldGroupPlugin;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\Filament\Pages;
use SolutionForest\InspireCms\Filament\Widgets;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\Helpers\UrlHelper;
use SolutionForest\InspireCms\Http\Middleware as CmsMiddleware;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use SolutionForest\InspireCms\Livewire\ListImportNExport;
use SolutionForest\InspireCms\Livewire\NavigationTree;
use SolutionForest\InspireCms\Support\Base\Filament\ThemeConfig;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id(InspireCmsConfig::getPanelId())
            ->globalSearch(app(LicenseManager::class)->canGlobalSearch());

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

        $this->configureResources($panel);
        $this->configurePages($panel);
        $this->configureWidgets($panel);
        $this->configureClusters($panel);
        $this->configureMiddleware($panel);
        $this->configurePlugins($panel);
        $this->configureNavigation($panel);
        $this->configureNotification($panel);
        $this->configureFilamentActions($panel);
        $this->registerLivewireComponents($panel);

        return $panel;
    }

    protected function configureClusters(Panel $panel): Panel
    {
        return $panel
            ->discoverClusters(in: app_path('Filament/Cms/Clusters'), for: 'App\\Filament\\Cms\\Clusters');
    }

    protected function configureWidgets(Panel $panel): Panel
    {
        return $panel
            ->widgets([
                Widgets\CmsInfoWidget::class,
                Widgets\CmsVersionInfo::class,
                Widgets\PageActivity::class,
                Widgets\UserActivity::class,
                Widgets\AlertOverview::class,
                Widgets\ThemeInfo::class,
                Widgets\TemplateInfo::class,
                ...InspireCmsConfig::get('admin.extra_widgets', []),
            ])
            ->discoverWidgets(in: app_path('Filament/Cms/Widgets'), for: 'App\\Filament\\Cms\\Widgets');
    }

    protected function configurePages(Panel $panel): Panel
    {
        return $panel
            ->pages(array_merge(
                array_values(InspireCmsConfig::getFilamentPages()),
                collect(inspirecms()->getSections())->map(fn (ClusterSection $section) => $section->getFqcn())->all()
            ))
            ->discoverPages(in: app_path('Filament/Cms/Pages'), for: 'App\\Filament\\Cms\\Pages');
    }

    protected function configureResources(Panel $panel): Panel
    {
        return $panel
            ->resources(InspireCmsConfig::getFilamentResources())
            ->discoverResources(in: app_path('Filament/Cms/Resources'), for: 'App\\Filament\\Cms\\Resources');
    }

    protected function configureMiddleware(Panel $panel): Panel
    {
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
            CmsMiddleware\LicenseCheck::class,
            CmsMiddleware\SetUpPoweredBy::class,
        ];
        $authMiddleware = [
            CmsMiddleware\CmsAuthenticate::class,
            CmsMiddleware\UserPreference::class,
        ];

        return $panel
            ->middleware($middleware)
            ->authMiddleware($authMiddleware);
    }

    protected function configurePlugins(Panel $panel): Panel
    {
        $plugins[] = FilamentPeekPlugin::make();
        $plugins[] = FilamentFieldGroupPlugin::make()
            ->enablePlugin()
            ->overrideResources([])
            ->fieldTypeConfigs(InspireCmsConfig::get('custom_fields.extra_config'), false);

        $translatablePlugin = SpatieLaravelTranslatablePlugin::make();
        $translatablePlugin->getLocaleLabelUsing(function ($locale, $displayLocale) {

            $lang = data_get(inspirecms()->getAllAvailableLanguages(), $locale);

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

        return $panel
            ->plugins($plugins);
    }

    protected function configureNavigation(Panel $panel): Panel
    {
        $position = InspireCmsConfig::get('admin.navigation_position', 'top');

        $userMenuItems = [
            MenuItem::make()
                ->label(fn () => __('inspirecms::inspirecms.version') . ': ' . InspireCms::version())
                ->icon(fn () => FilamentIcon::resolve('inspirecms::info'))
                ->url('#'),

            MenuItem::make()
                ->label('Create license')
                ->icon('heroicon-o-key')
                ->url('https://inspirecms.net/#pricing', true),
        ];

        return $panel
            ->topNavigation($position == 'top')
            ->navigationGroups([
                'content' => NavigationGroup::make(fn () => __('inspirecms::inspirecms.content.plural')),
                'media' => NavigationGroup::make(fn () => __('inspirecms::inspirecms.media')),
                'settings' => NavigationGroup::make(fn () => __('inspirecms::inspirecms.settings')),
                'users' => NavigationGroup::make(fn () => __('inspirecms::inspirecms.user.plural')),
            ])
            ->userMenuItems($userMenuItems)
            ->bootUsing(function () {
                FilamentView::registerRenderHook(
                    PanelsRenderHook::USER_MENU_BEFORE,
                    function () {
                        $links = [];
                        $links[] = UIHelper::generateIconButton(
                            icon: 'heroicon-o-book-open',
                            color: 'gray',
                            url: InspireCms::URL_DOCUMENTATION,
                            size: 'lg',
                            attributes: [
                                'target' => '_blank',
                                'rel' => 'noopener noreferrer',
                                'class' => 'px-0.5',
                                'alt' => 'Documentation',
                                'title' => 'Documentation',
                            ]
                        )->toHtml();
                        $links[] = UIHelper::generateIconButton(
                            icon: 'heroicon-o-globe-alt',
                            color: 'gray',
                            url: config('app.url'),
                            size: 'lg',
                            attributes: [
                                'target' => '_blank',
                                'rel' => 'noopener noreferrer',
                                'class' => 'px-0.5',
                                'alt' => 'View Website',
                                'title' => 'View Website',
                            ]
                        )->toHtml();

                        return implode('', $links);
                    }
                );
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

    protected function registerLivewireComponents(Panel $panel): Panel
    {
        return $panel->livewireComponents([
            ListImportNExport::class,
            NavigationTree::class,
        ])->bootUsing(function () {
            Livewire::component('inspirecms::navigation-tree', NavigationTree::class);
        });
    }

    protected function configureFilamentActions(Panel $panel): Panel
    {
        return $panel->bootUsing(function () {

            // Confiure alignment
            Action::configureUsing(function (Action $action) {
                $action->modalFooterActionsAlignment(Alignment::End);
            });
            TablesAction::configureUsing(function (TablesAction $action) {
                $action->modalFooterActionsAlignment(Alignment::End);
            });
            FormComponentsAction::configureUsing(function (FormComponentsAction $action) {
                $action->modalFooterActionsAlignment(Alignment::End);
            });

            foreach (InspireCmsConfig::getFilamentPages() as $page) {
                $page::alignFormActionsEnd();
            }

            EditAction::configureUsing(function (EditAction $action) {
                $action->icon(function (EditAction $action) {
                    return $action->isIconButton()
                        ? FilamentIcon::resolve('inspirecms::edit')
                        : null;
                });
            });
            ViewAction::configureUsing(function (ViewAction $action) {
                $action->icon(function (ViewAction $action) {
                    return $action->isIconButton()
                        ? FilamentIcon::resolve('inspirecms::visible')
                        : null;
                });
            });
            DeleteAction::configureUsing(function (DeleteAction $action) {
                $action->icon(function (DeleteAction $action) {
                    return $action->isIconButton()
                        ? FilamentIcon::resolve('inspirecms::delete')
                        : null;
                });
            });
            ForceDeleteAction::configureUsing(function (ForceDeleteAction $action) {
                $action->icon(function (ForceDeleteAction $action) {
                    return $action->isIconButton()
                        ? FilamentIcon::resolve('inspirecms::delete')
                        : null;
                });
            });
            RestoreAction::configureUsing(function (RestoreAction $action) {
                $action->icon(function (RestoreAction $action) {
                    return $action->isIconButton()
                        ? FilamentIcon::resolve('inspirecms::restore')
                        : null;
                });
            });
            TablesReplicateAction::configureUsing(function (TablesReplicateAction $action) {
                $action
                    ->color('gray')
                    ->modalIcon(FilamentIcon::resolve('inspirecms::clone'));
            });
            PreviewAction::configureUsing(function (PreviewAction $action) {
                $action->icon(FilamentIcon::resolve('inspirecms::preview'));
            });
            InlinePreviewAction::configureUsing(function (InlinePreviewAction $action) {
                $action->icon(FilamentIcon::resolve('inspirecms::preview'));
            });
        });
    }
}
