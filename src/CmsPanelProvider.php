<?php

namespace SolutionForest\InspireCms;

use Closure;
use Filament\Http\Middleware\Authenticate;
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
            ->id(config('insiprecms.filament.panel_id', 'cms'))
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
                        \SolutionForest\InspireCms\FieldTypes\Configs\Translate::class,
                        \SolutionForest\InspireCms\FieldTypes\Configs\ContentPicker::class,
                    ], false),
                FilamentPeekPlugin::make(),
                \Filament\SpatieLaravelTranslatablePlugin::make(),
                new InspireCmsTheme,
            ])
            ->resources(config('inspirecms.filament.resources'))
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
            ->topNavigation()
            ->navigationGroups([
                NavigationGroup::make(fn () => __('inspirecms::inspirecms.content')),
                NavigationGroup::make(fn () => __('inspirecms::inspirecms.settings')),
                NavigationGroup::make(fn () => __('inspirecms::inspirecms.users')),
            ]);
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
