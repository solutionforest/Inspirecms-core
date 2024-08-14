<?php

namespace SolutionForest\InspireCms;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use SolutionForest\FilamentFieldGroup\FilamentFieldGroupPlugin;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('cms')
            ->path('cms')
            ->default()
            ->login()
            ->plugins([
                FilamentFieldGroupPlugin::make()->enablePlugin()->overrideResources([]),
                new InspireCmsTheme,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn () => __('inspirecms-core::inspirecms-core.content')),
                NavigationGroup::make()
                    ->label(fn () => __('inspirecms-core::inspirecms-core.setting')),
            ])
            ->discoverResources(in: app_path('Cms/Resources'), for: 'App\\Cms\\Resources')
            ->discoverPages(in: app_path('Cms/Pages'), for: 'App\\Cms\\Pages')
            ->discoverClusters(in: app_path('Cms/Clusters'), for: 'App\\Cms\\Clusters')
            ->discoverWidgets(in: app_path('Cms/Widgets'), for: 'App\\Cms\\Widgets')
            ->resources(config('inspirecms-core.resources', []))
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
    }
}
