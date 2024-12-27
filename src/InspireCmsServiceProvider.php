<?php

namespace SolutionForest\InspireCms;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Theme;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Events as AuthEvents;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Livewire\Features\SupportTesting\Testable;
use SolutionForest\InspireCms\Base as InspireCmsBase;
use SolutionForest\InspireCms\Base\Manifests as BaseManifests;
use SolutionForest\InspireCms\Http\Responses\Auth\RegistrationResponse;
use SolutionForest\InspireCms\Support\Models as SupportModels;
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
                    ->askToStarRepoOnGitHub('solution-forest/inspirecms')
                    ->startWith(function (InstallCommand $command) {
                        $command->call(Commands\InstallRequirePacakges::class);
                    })
                    ->endWith(function (InstallCommand $command) {
                        $command->call('migrate');
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

        $this->app->singleton(BaseManifests\ModelManifestInterface::class, fn () => $this->app->make(BaseManifests\ModelManifest::class));
        $this->app->singleton(BaseManifests\ContentStatusManifestInterface::class, fn () => $this->app->make(BaseManifests\ContentStatusManifest::class));
        $this->app->singleton(BaseManifests\PermissionManifestInterface::class, fn () => $this->app->make(BaseManifests\PermissionManifest::class));
        $this->app->singleton(BaseManifests\LocaleManifestInterface::class, fn () => $this->app->make(BaseManifests\LocaleManifest::class));
        $this->app->singleton(InspireCmsBase\TemplateManagerInterface::class, fn () => $this->app->make(InspireCmsBase\TemplateManager::class));

        $this->app->singleton(Services\AssetServiceInterface::class, fn () => $this->app->make(Services\AssetService::class));
        $this->app->singleton(Services\ContentServiceInterface::class, fn () => $this->app->make(Services\ContentService::class));
        $this->app->singleton(Services\ImportDataServiceInterface::class, fn () => $this->app->make(Services\ImportDataService::class));
        $this->app->singleton(Services\ImportServiceInterface::class, fn () => $this->app->make(Services\ImportService::class));

        $this->app->singleton(\Filament\Navigation\NavigationItem::class, \SolutionForest\InspireCms\Filament\Navigation\NavigationItem::class);

        \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig::mixin(new \SolutionForest\InspireCms\Fields\TranslatableFieldType);

        $this->app->bind(RegistrationResponseContract::class, RegistrationResponse::class);

        Facades\ModelManifest::register();
        $supportModels = $this->getConfigSupoortModels();
        Facades\ModelManifest::replace(
            SupportModels\Contracts\MediaAsset::class,
            $supportModels['media_asset']
        );
        Facades\ModelManifest::replace(
            SupportModels\Contracts\NestableTree::class,
            $supportModels['nestable_tree']
        );

        $this->customPlugins();
    }

    public function bootingPackage(): void
    {
        Facades\ModelManifest::registerMorphMap();
        Facades\ModelManifest::registerPolices();
        $this->registerSupport();
        $this->registerAuthGuard();

        $this->registerEvents();

        $this->registerScheduleCommands();
    }

    public function packageBooted(): void
    {
        $this->configureFilamentForm();

        \Livewire\Livewire::component('inspirecms::content-sidebar', \SolutionForest\InspireCms\Livewire\ContentSidebar::class);
        \Livewire\Livewire::component('inspirecms::document-type-paginator', \SolutionForest\InspireCms\Livewire\DocumentTypePaginator::class);

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

        if (app()->runningInConsole()) {
            $this->registerStubs();
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
        return [
            Theme::make('inspirecms', __DIR__ . '/../resources/dist/inspirecms.css'),
            Js::make('inspirecms', __DIR__ . '/../resources/dist/inspirecms.js'),
        ];
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
            Commands\ExecuteImport::class,
            Commands\DataCleanup::class,
            Commands\RepairPermissionsCommand::class,
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
            'inspirecms::add' => 'heroicon-o-plus-small',
            'inspirecms::attach' => 'heroicon-o-link',
            'inspirecms::json-file' => view('inspirecms::icons.json-file'),
            'inspirecms::fields' => view('inspirecms::icons.fields'),
            'inspirecms::templates' => view('inspirecms::icons.templates'),
            'inspirecms::document-type' => view('inspirecms::icons.document-type'),
        ];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return ['cms'];
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
        $map = Arr::pluck(InspireCmsConfig::get('models'), 'fqcn', 'polymorphic_type');

        if (! empty($map)) {
            Relation::enforceMorphMap($map);
        }
    }

    protected function customPlugins(): void
    {
        if (InspireCmsConfig::get('override_plugins.field_group_models', false)) {

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
            'model' => Facades\ModelManifest::get(
                \SolutionForest\InspireCms\Models\Contracts\User::class,
                \SolutionForest\InspireCms\Models\User::class,
            ),
        ]);
        config()->set('auth.guards.inspirecms', [
            'driver' => 'session',
            'provider' => InspireCmsConfig::getGuardName(),
        ]);
    }

    protected function registerEvents(): void
    {
        //region User Auth Activity
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
        //endregion User Auth Activity

        //region Content
        Event::listen(
            Events\Content\UpdatePath::class,
            [Listeners\Content\ProcessContentPath::class, 'handleUpsert']
        );
        Event::listen(Events\Content\CreatingContentVersion::class, Listeners\Content\UnpubilshChildren::class);
        Event::listen(Events\Content\DispatchContentVersion::class, Listeners\Content\ProcessContentVersion::class);
        Event::listen(Events\Content\GenerateSitemap::class, Listeners\Content\GenerateContentSitemap::class);
        //endregion Content
    }

    protected function configureFilamentForm(): void
    {
        \Filament\Forms\Components\Field::macro('limitLengthWithHint', function (int | \Closure $length) {
            return $this->live()
                ->hint(function ($state, $component) {
                    if (! is_string($state)) {
                        return '';
                    }

                    return __('inspirecms::inspirecms.hints.remaining_xxx_characters', ['number' => $component->getMaxLength() - strlen($state)]);
                })
                ->maxLength($length);
        });
        \Filament\Forms\Components\Field::macro('translatable', function () {
            return $this
                ->hintIcon('heroicon-m-language', __('inspirecms::inspirecms.translatable'));
        });
    }

    protected function registerSupport(): void
    {
        Support\Facades\ModelRegistry::replace(
            SupportModels\Contracts\MediaAsset::class,
            Facades\ModelManifest::get(SupportModels\Contracts\MediaAsset::class)
        );
        Support\Facades\ModelRegistry::replace(
            SupportModels\Contracts\NestableTree::class,
            Facades\ModelManifest::get(SupportModels\Contracts\NestableTree::class)
        );
        Support\Facades\MediaLibraryRegistry::setDisk(InspireCmsConfig::get('media_library.disk'));
        Support\Facades\MediaLibraryRegistry::setDirectory(InspireCmsConfig::get('media_library.directory'));
        Support\Facades\MediaLibraryRegistry::setThumbnailCrop(InspireCmsConfig::get('media_library.thumbnail.width'), InspireCmsConfig::get('media_library.thumbnail.height'));
        Support\Facades\MediaLibraryRegistry::setShouldMapVideoPropertiesWithFfmpeg(boolval(InspireCmsConfig::get('media_library.should_map_video_properties_with_ffmpeg', false)));

        Support\Facades\InspireCmsSupport::setTablePrefix(InspireCmsConfig::get('models.table_name_prefix'));
        Support\Facades\InspireCmsSupport::setAuthGuard(InspireCmsConfig::get('auth.guard'));

        Support\Facades\ResolverRegistry::set('user', InspireCmsConfig::get('resolvers.user', \SolutionForest\InspireCms\Support\Resolver\UserResolver::class));
    }

    protected function getConfigSupoortModels(): array
    {
        return Arr::only(InspireCmsConfig::get('models.fqcn'), [
            'media_asset',
            'nestable_tree',
        ]);
    }

    protected function registerScheduleCommands(): void
    {
        $schedule = $this->app[\Illuminate\Console\Scheduling\Schedule::class];

        $tasks = InspireCmsConfig::get('scheduled_tasks', []);

        foreach ($tasks as $taskKey => $task) {

            try {

                if (! is_array($task)) {
                    continue;
                }

                if (! ($task['enabled'] ?? false) ||
                    ! isset($task['schedule'])
                ) {
                    continue;
                }

                $func = $task['schedule'];

                $command = $task['command'] ?? null;

                $arguments = $task['arguments'] ?? [];

                if (blank($command) || ! is_string($command)) {
                    break;
                }

                $schedule->command($command, $arguments)->{$func}();

            } catch (\Throwable $th) {
                //
            }

        }
    }

    protected function registerStubs()
    {
        // Handle Stubs
        foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
            $this->publishes([
                $file->getRealPath() => base_path("stubs/inspirecms/{$file->getFilename()}"),
            ], 'inspirecms-stubs');
        }

        foreach (app(Filesystem::class)->allFiles(__DIR__ . '/../stubs/SampleAssets/Views') as $file) {

            $dir = str($file->getRelativePath())
                ->replace(['Components/Themes'], ['components/' . inspirecms_templates()->getComponentPrefix()])
                ->explode('/')
                ->map(fn ($path) => (string) str($path)->kebab())
                ->implode('/');

            $fullPath = (string) str(base_path('resources/views'))
                ->finish('/')
                ->when(filled($dir), fn ($str) => $str->finish($dir)->finish('/'))
                ->finish(str($file->getFilenameWithoutExtension())->kebab()->finish('.blade.php'));

            $this->publishes([
                $file->getRealPath() => $fullPath,
            ], 'inspirecms-sample-assets');
        }
        foreach (app(Filesystem::class)->allFiles(__DIR__ . '/../stubs/SampleAssets/Assets') as $file) {
            $dir = str($file->getRelativePath())->explode('/')
                ->map(fn ($path) => (string) str($path)->kebab())
                ->implode('/');
            $fullPath = (string) str(public_path($dir))
                ->finish('/')
                ->finish(str($file->getFilename())->kebab()->beforeLast('.stub'));
            $this->publishes([
                $file->getRealPath() => $fullPath,
            ], 'inspirecms-sample-assets');
        }
    }
}
