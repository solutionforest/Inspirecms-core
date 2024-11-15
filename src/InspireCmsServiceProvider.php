<?php

namespace SolutionForest\InspireCms;

use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Events as AuthEvents;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Livewire\Features\SupportTesting\Testable;
use SolutionForest\InspireCms\Base\Assets as BaseAssets;
use SolutionForest\InspireCms\Base\Manifests as BaseManifests;
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

        $this->app->scoped(BaseAssets\InspireCmsAssetManagerInterface::class, fn () => $this->app->make(BaseAssets\InspireCmsAssetManager::class));

        $this->app->singleton(BaseManifests\ModelManifestInterface::class, fn () => $this->app->make(BaseManifests\ModelManifest::class));
        $this->app->singleton(BaseManifests\ContentStatusManifestInterface::class, fn () => $this->app->make(BaseManifests\ContentStatusManifest::class));
        $this->app->singleton(BaseManifests\PermissionManifestInterface::class, fn () => $this->app->make(BaseManifests\PermissionManifest::class));
        $this->app->singleton(BaseManifests\LocaleManifestInterface::class, fn () => $this->app->make(BaseManifests\LocaleManifest::class));

        $this->app->singleton(Services\ContentServiceInterface::class, fn () => $this->app->make(Services\ContentService::class));

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
    }

    public function bootingPackage(): void
    {
        Facades\ModelManifest::registerMorphMap();
        Facades\ModelManifest::registerPolices();
        $this->registerSupport();
        $this->customPlugins();
        $this->registerAuthGuard();

        $this->registerEvents();

        $this->registerScheduleCommands();
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
            
            foreach (app(Filesystem::class)->allFiles(__DIR__ . '/../stubs/SampleViews') as $file) {

                $dir = collect(explode('/', $file->getRelativePath()))->map(fn ($path) => str($path)->kebab())->implode('/');
                $viewName = str($file->getFilenameWithoutExtension())->kebab()->finish('.blade.php');

                $viewFullPath = (string) str(str(base_path('resources/views')))
                    ->finish('/')
                    ->when(filled($dir), fn ($str) => $str->finish($dir)->finish('/'))
                    ->finish($viewName);

                $this->publishes([
                    $file->getRealPath() => $viewFullPath,
                ], 'inspirecms-sample-views');
            }
            
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/SampleTemplates') as $file) {
                $templateFullPath =  (string) str(config('inspirecms.template.path'))
                    ->finish('/')
                    ->finish(str($file->getFilenameWithoutExtension())->kebab()->finish('.blade.php'));
                $this->publishes([
                    $file->getRealPath() => $templateFullPath,
                ], 'inspirecms-sample-templates');
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
            Commands\ImportSampleData::class,
            Commands\CleanupContentVersion::class,
            Commands\RefreshIndexes::class,
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
        if (InspireCmsConfig::get('override_plugins.field_group_models', false)) {

            // override field group models
            \SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup::setFieldGroupModelClass(
                \SolutionForest\InspireCms\Models\FieldGroup::class
            );
            \SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup::setFieldModelClass(
                \SolutionForest\InspireCms\Models\Field::class
            );
        }

        if (InspireCmsConfig::get('override_plugins.scout', true)) {

            $indexSettings = config('scout.meilisearch.index-settings', []);

            if (InspireCmsConfig::get('indexes.content.enabled', true)) {
                $indexSettings[InspireCmsConfig::getContentModelClass()] = InspireCmsConfig::get('indexes.content.index_settings', []);
            }

            config()->set('scout.meilisearch.index-settings', $indexSettings);
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
        Support\Facades\MediaLibraryManifest::setModel(
            Facades\ModelManifest::get(SupportModels\Contracts\MediaAsset::class)
        );
        Support\Facades\MediaLibraryManifest::setDisk(config('inspirecms.media_library.disk'));
        Support\Facades\MediaLibraryManifest::setDirectory(config('inspirecms.media_library.directory'));
        Support\Facades\MediaLibraryManifest::setThumbnailCrop(config('inspirecms.media_library.thumbnail.width'), config('inspirecms.media_library.thumbnail.height'));

        Support\Facades\InspireCmsSupport::setTablePrefix(config('inspirecms.models.table_name_prefix'));
        Support\Facades\InspireCmsSupport::setNestableTreeModel(
            Facades\ModelManifest::get(SupportModels\Contracts\NestableTree::class)
        );

        Support\Facades\ResolverManifest::set('user', config('inspirecms.resolvers.user', \SolutionForest\InspireCms\Support\Resolver\UserResolver::class));
    }

    protected function getConfigSupoortModels(): array
    {
        return Arr::only(config('inspirecms.models.fqcn'), [
            'media_asset',
            'nestable_tree',
        ]);
    }

    protected function registerScheduleCommands(): void
    {
        $schedule = $this->app[\Illuminate\Console\Scheduling\Schedule::class];

        $tasks = Arr::only(config('inspirecms.scheduled_tasks', []), [
            'cleanup_content_verion',
        ]);

        foreach ($tasks as $taskKey => $task) {

            if (! is_array($task)) {
                continue;
            }

            if (! ($task['enabled'] ?? false) ||
                ! isset($task['schedule'])
            ) {
                continue;
            }

            $func = $task['schedule'];

            switch ($taskKey) {
                case 'cleanup_content_verion':

                    $command = $task['command'] ?? null;

                    if (blank($command) || ! is_string($command) || ($command && ! class_exists($command))) {
                        break;
                    }

                    // check have this func
                    if (method_exists($schedule->command($command), $func)) {

                        $schedule->command($command)->{$func}();
                    }

                    break;
            }

        }
    }
}
