<?php

namespace SolutionForest\InspireCms;

use Closure;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Alignment;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Pboivin\FilamentPeek\FilamentPeekPlugin;
use SolutionForest\FilamentFieldGroup\FilamentFieldGroupPlugin;
use SolutionForest\InspireCms\Filament\Pages;
use SolutionForest\InspireCms\Filament\Widgets;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id('cms')
            ->path('cms')
            ->default()
            ->brandName('InspireCms')->brandLogo(fn () => view('inspirecms::logo'))
            ->authGuard(InspireCmsConfig::getGuardName())
            ->login(Pages\Auth\Login::class)
            ->profile(Pages\Auth\EditProfile::class)
            ->routes($this->getExtraRoutes())
            ->homeUrl(fn () => Pages\Dashboard::getUrl())
            ->plugins([
                FilamentFieldGroupPlugin::make()
                    ->enablePlugin()
                    ->overrideResources([])
                    ->fieldTypeConfigs([
                        \SolutionForest\InspireCms\FieldTypes\Configs\ContentPicker::class,
                        \SolutionForest\InspireCms\FieldTypes\Configs\ContentChildrenPicker::class,
                    ], false),
                FilamentPeekPlugin::make(),
                new InspireCmsTheme,
            ])
            ->resources(config('inspirecms.resources'))
            ->pages([
                Pages\Dashboard::class,
                ...\SolutionForest\InspireCms\Facades\InspireCms::getSections()
                    ->map(fn (\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection $section) => $section->getFqcn())
                    ->all(),
            ])
            ->widgets([
                Widgets\PageActivity::class,
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
            ]);

        $this->configureNavigation($panel);
        $this->configureFilamentActions($panel);
        $this->registerLivewireComponents($panel);

        return $panel;
    }

    protected function configureNavigation(Panel $panel): Panel
    {
        return $panel
            ->topNavigation();
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
