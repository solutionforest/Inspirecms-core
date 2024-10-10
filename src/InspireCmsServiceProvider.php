<?php

namespace SolutionForest\InspireCms;

use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Events as AuthEvents;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Livewire\Features\SupportTesting\Testable;
use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifest;
use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\LocaleManifest;
use SolutionForest\InspireCms\Base\Manifests\LocaleManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\ModelManifest;
use SolutionForest\InspireCms\Base\Manifests\ModelManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifest;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifestInterface;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Testing\TestsInspireCms;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InspireCmsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'inspirecms';

    public static string $viewNamespace = 'inspirecms';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasRoutes($this->getRoutes())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('solutionforest/inspirecms')
                    ->startWith(function (InstallCommand $command) {
                        $command->call(Commands\InstallRequirePacakges::class);
                    })
                    ->endWith(function (InstallCommand $command) {
                        $command->call(Commands\PublishPanel::class);
                        $command->call(Commands\ImportDefaultData::class);
                    });

            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->registerPolymorphism();

        $this->app->singleton(ModelManifestInterface::class, fn () => $this->app->make(ModelManifest::class));
        $this->app->singleton(ContentStatusManifestInterface::class, fn () => $this->app->make(ContentStatusManifest::class));
        $this->app->singleton(PermissionManifestInterface::class, fn () => $this->app->make(PermissionManifest::class));
        $this->app->singleton(LocaleManifestInterface::class, fn () => $this->app->make(LocaleManifest::class));

        \SolutionForest\InspireCms\Facades\ModelManifest::register();
    }

    public function bootingPackage(): void
    {
        \SolutionForest\InspireCms\Facades\ModelManifest::registerMorphMap();
        \SolutionForest\InspireCms\Facades\ModelManifest::registerPolices();
        $this->customPlugins();
        $this->registerAuthGuard();

        Blueprint::mixin(new \SolutionForest\InspireCms\Macros\BlueprintMarcos);

        Event::listen(
            AuthEvents\Login::class,
            [Listeners\UserAuthActivityListener::class, 'login']
        );
        Event::listen(
            AuthEvents\Logout::class,
            [Listeners\UserAuthActivityListener::class, 'logout']
        );
        Event::listen(
            AuthEvents\Failed::class,
            [Listeners\UserAuthActivityListener::class, 'loginFailed']
        );
        Event::listen(
            AuthEvents\PasswordReset::class,
            [Listeners\UserAuthActivityListener::class, 'passwordReset']
        );
    }

    public function packageBooted(): void
    {
        $this->configureFilamentForm();

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/inspirecms/{$file->getFilename()}"),
                ], 'inspirecms-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsInspireCms);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'solution-forest/inspirecms';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            Commands\PublishPanel::class,
            Commands\InstallRequirePacakges::class,
            Commands\ImportDefaultData::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [
            'inspirecms::preview' => 'heroicon-o-eye',
            'inspirecms::goto' => 'heroicon-o-arrow-right-end-on-rectangle',
            'inspirecms::reset' => 'heroicon-o-arrow-path',
            'inspirecms::clone' => 'heroicon-o-document-duplicate',
        ];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return ['api'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_inspire-cms-core_table',
        ];
    }

    /**
     * Register Polymorphic Types
     */
    protected function registerPolymorphism(): void
    {
        $map = Arr::pluck(config('inspirecms.models'), 'fqcn', 'polymorphic_type');

        if (! empty($map)) {
            Relation::enforceMorphMap($map);
        }
    }

    protected function customPlugins(): void
    {
        if (config('inspirecms.override_plugins.field_group_models', false)) {

            // override field group models
            \SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup::setFieldGroupModelClass(
                \SolutionForest\InspireCms\Models\FieldGroup::class
            );
            \SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup::setFieldModelClass(
                \SolutionForest\InspireCms\Models\Field::class
            );
        }
    }

    protected function registerAuthGuard(): void
    {
        config()->set('auth.providers.inspirecms', [
            'driver' => 'eloquent',
            'model' => \SolutionForest\InspireCms\Facades\ModelManifest::get(
                \SolutionForest\InspireCms\Models\Contracts\User::class,
                \SolutionForest\InspireCms\Models\User::class,
            ),
        ]);
        config()->set('auth.guards.inspirecms', [
            'driver' => 'session',
            'provider' => InspireCmsConfig::getGuardName(),
        ]);
    }

    protected function configureFilamentForm(): void
    {
        \Filament\Forms\Components\Field::macro('limitLengthWithHint', function (int | \Closure $length) {
            return $this->live()
                ->hint(fn ($state, $component) => __('inspirecms::inspirecms.hints.remaining_xxx_characters', ['number' => $component->getMaxLength() - strlen($state)]))
                ->maxLength($length);
        });
        \Filament\Forms\Components\Field::macro('translatable', function () {
            return $this
                ->hintIcon('heroicon-m-language', __('inspirecms::inspirecms.translatable'));
        });
    }
}
