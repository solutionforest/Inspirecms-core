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
use SolutionForest\InspireCms\Filament\Widgets;
use SolutionForest\InspireCms\Filament\Pages;
use SolutionForest\InspireCms\Filament\Resources;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id('cms')
            ->path('cms')
            ->default()
            ->brandName('InspireCms')->brandLogo(fn () => view('inspirecms::logo'))
            ->login()
            ->homeUrl(fn () => Pages\Dashboard::getUrl())
            ->plugins([
                FilamentFieldGroupPlugin::make()->enablePlugin()->overrideResources([]),
                new InspireCmsTheme,
            ])
            ->resources(config('inspirecms.resources', [
                Resources\Settings\DocumentTypeResource::class,
                Resources\Settings\FieldGroupResource::class,
                Resources\Contents\PageResource::class,
            ]))
            ->pages([
                Pages\Dashboard::class,
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

        return $panel;
    }

    protected function configureNavigation(Panel $panel): Panel
    {
        return $panel
            ->topNavigation()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn () => __('inspirecms::inspirecms.content'))
                    // Child navigation must haven't navigationIcon
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make()
                    ->label(fn () => __('inspirecms::inspirecms.setting'))
                    // Child navigation must haven't navigationIcon
                    ->icon('heroicon-o-cog-6-tooth'),
            ]);
    }
}
