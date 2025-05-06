<?php

namespace SolutionForest\InspireCms;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Theme;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Events as AuthEvents;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\InspireCms\Base as InspireCmsBase;
use SolutionForest\InspireCms\Base\Manifests as BaseManifests;
use SolutionForest\InspireCms\Fields\PropertyValueTransformer;
use SolutionForest\InspireCms\Fields\PropertyValueTransformerInterface;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\Http\Middleware\CmsAuthenticate;
use SolutionForest\InspireCms\Http\Responses\Auth\RegistrationResponse;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use SolutionForest\InspireCms\Support\Models as SupportModels;
use SolutionForest\InspireCms\Testing\TestsInspireCms;
use SolutionForest\InspireCms\View\Components as ViewComponents;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InspireCmsServiceProvider extends PackageServiceProvider
{
    public static string $name = InspireCms::CORE_SLUG;

    public static string $viewNamespace = InspireCms::CORE_SLUG;

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
                    ->addOption(
                        name: 'skip-samples',
                        shortcut: 's',
                        description: 'Skip importing sample data',
                    )
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->startWith(function (InstallCommand $command) {
                        $command->call(Commands\InstallRequirePacakges::class);
                    })
                    ->endWith(function (InstallCommand $command) {
                        $command->call('migrate');
                        $command->call(Commands\PublishPanel::class);
                        $command->call(Commands\ImportDefaultData::class, [
                            '--skip-samples' => $command->option('skip-samples'),
                        ]);
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

        if (file_exists($package->basePath('/../resources/routes'))) {
            $package->hasRoutes($this->getRoutes());
        }
    }

    public function registeringPackage(): void
    {
        // Register support package first
        $this->app->register(Support\InspireCmsSupportServiceProvider::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(BaseManifests\ModelManifestInterface::class, fn () => $this->app->make(BaseManifests\ModelManifest::class));
        $this->app->singleton(BaseManifests\ContentStatusManifestInterface::class, fn () => $this->app->make(BaseManifests\ContentStatusManifest::class));
        $this->app->singleton(BaseManifests\PermissionManifestInterface::class, fn () => $this->app->make(BaseManifests\PermissionManifest::class));
        $this->app->singleton(BaseManifests\LocaleManifestInterface::class, fn () => $this->app->make(BaseManifests\LocaleManifest::class));

        $this->app->singleton(InspireCmsBase\TemplateManagerInterface::class, fn () => $this->app->make(InspireCmsBase\TemplateManager::class));

        $this->app->singleton(InspireCmsBase\KeyValueCache::class, fn () => new InspireCmsBase\KeyValueCache($this->app['cache']));

        $this->app->singleton(LicenseManager::class, fn () => new LicenseManager);

        $this->app->singleton(Services\AssetServiceInterface::class, fn () => $this->app->make(Services\AssetService::class));
        $this->app->singleton(Services\ContentServiceInterface::class, fn () => $this->app->make(Services\ContentService::class));
        $this->app->singleton(Services\ImportDataServiceInterface::class, fn () => $this->app->make(Services\ImportDataService::class));
        $this->app->singleton(Services\ImportServiceInterface::class, fn () => $this->app->make(Services\ImportService::class));
        $this->app->singleton(Services\ExportServiceInterface::class, fn () => $this->app->make(Services\ExportService::class));

        $this->app->singleton(PropertyValueTransformerInterface::class, PropertyValueTransformer::class);

        $this->app->singleton(\Filament\Navigation\NavigationItem::class, \SolutionForest\InspireCms\Filament\Navigation\NavigationItem::class);

        \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig::mixin(new \SolutionForest\InspireCms\Fields\Mixins\FieldTypeDefinition);

        $this->app->bind(RegistrationResponseContract::class, RegistrationResponse::class);

        $this->registerModels();

        $this->customPlugins();

        $this->registerSupport();

        $this->registerAuthGuard();

    }

    public function bootingPackage(): void
    {
        Facades\ModelManifest::registerMorphMap();
        Facades\ModelManifest::registerPolices();

        $this->registerComponentAndDirectives();

        $this->registerEvents();

        $this->registerScheduleCommands();
    }

    public function packageBooted(): void
    {
        $this->configureFilamentForm();

        \Livewire\Livewire::component('inspirecms::content-sidebar', \SolutionForest\InspireCms\Livewire\ContentSidebar::class);
        \Livewire\Livewire::component('inspirecms::content-tree-node', \SolutionForest\InspireCms\Livewire\ContentTreeNode::class);
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

        \Livewire\Livewire::addPersistentMiddleware([
            CmsAuthenticate::class,
        ]);

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        $this->addAboutPluginInfo();

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
            Commands\AboutCommand::class,
            Commands\CacheStats::class,
            Commands\GenerateSitemap::class,
            Commands\UpdatePluginCommand::class,
            Commands\PublishPanel::class,
            Commands\InstallRequirePacakges::class,
            Commands\ImportDefaultData::class,
            Commands\ExecuteImport::class,
            Commands\ExecuteExport::class,
            Commands\DataCleanup::class,
            Commands\RepairPermissionsCommand::class,
            Commands\ListRoutes::class,
            Commands\ClearCache::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        $iconPrefix = 'inspirecms::';

        return collect([

            'preview' => 'heroicon-o-eye',

            'visible' => 'heroicon-m-eye',
            'invisiable' => 'heroicon-o-eye-slash',
            'locked' => 'heroicon-o-lock-closed',
            'unlocked' => 'heroicon-o-lock-open',

            'goto' => 'heroicon-m-arrow-top-right-on-square',

            'export' => 'heroicon-m-arrow-top-right-on-square',

            'back' => 'heroicon-o-chevron-left',
            'sort' => 'heroicon-o-arrows-up-down',
            'move_up' => 'heroicon-m-arrow-up',
            'move_down' => 'heroicon-m-arrow-down',
            'setting' => 'heroicon-s-cog-8-tooth',

            'as_default' => 'heroicon-o-star',
            'recycle_bin' => 'heroicon-o-trash',
            'theme' => 'heroicon-o-paint-brush',
            'language' => 'heroicon-o-language',
            'email' => 'heroicon-m-envelope',

            'json_file' => view('inspirecms::icons.json-file'),
            'fields' => view('inspirecms::icons.fields'),
            'templates' => view('inspirecms::icons.templates'),
            'document_type' => view('inspirecms::icons.document-type'),

        ])->mapWithKeys(fn ($icon, $key) => ["{$iconPrefix}{$key}" => $icon])->all();
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [
            'inspirecms',
        ];
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
            'create_inspire-cms-import_and_export_table',
            'create_custom_spatie_permission_table',
            'update_sessions_table',
            'update_notification_table_for_uuid_users',
        ];
    }

    protected function registerModels(): void
    {
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

    protected function customPlugins(): void
    {
        if (InspireCmsConfig::get('system.override_plugins.field_group_models', false)) {

            // override field group models
            FilamentFieldGroup::setFieldGroupModelClass(
                \SolutionForest\InspireCms\Models\FieldGroup::class
            );
            FilamentFieldGroup::setFieldModelClass(
                \SolutionForest\InspireCms\Models\Field::class
            );

            // customizing the config form schema
            FilamentFieldGroup::configureFieldTypeConfigFormUsing(
                \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\File::class,
                fn ($field, array $schema) => static::configureFileFieldTypeConfigFormSchema($schema)
            );
            FilamentFieldGroup::configureFieldTypeConfigFormUsing(
                \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image::class,
                fn ($field, array $schema) => static::configureFileFieldTypeConfigFormSchema($schema)
            );
        }
        if (InspireCmsConfig::get('system.override_plugins.spatie_permission', false)) {

            config()->set('permission.enable_wildcard_permission', true);

        }
    }

    protected function registerAuthGuard(): void
    {
        $guardName = AuthHelper::guardName();
        $authProvider = AuthHelper::providerName();
        $passwordBroker = AuthHelper::passwordBrokerName();

        if (! array_key_exists($authProvider, config('auth.providers'))) {

            $providerConfig = Arr::only(InspireCmsConfig::get('auth.provider', [
                'driver' => 'eloquent',
                'model' => Facades\ModelManifest::get(
                    \SolutionForest\InspireCms\Models\Contracts\User::class,
                    \SolutionForest\InspireCms\Models\User::class,
                ),
            ]), ['driver', 'model']);

            config()->set('auth.providers.' . $authProvider, $providerConfig);
        }

        if (! array_key_exists($guardName, config('auth.guards'))) {

            $guardConfig = Arr::only(InspireCmsConfig::get('auth.guard', [
                'driver' => 'session',
                'provider' => $authProvider,
            ]), ['driver', 'provider']);

            config()->set('auth.guards.' . $guardName, $guardConfig);
        }

        if (AuthHelper::enablePasswordReset() && ! array_key_exists($passwordBroker, config('auth.passwords'))) {

            $passwordConfig = Arr::only(InspireCmsConfig::get('auth.resetting_password', [
                'provider' => $authProvider,
                'table' => 'password_reset_tokens',
                'expire' => 60,
                'throttle' => 60,
            ]), ['provider', 'table', 'expire', 'throttle']);

            config()->set('auth.passwords.' . $passwordBroker, $passwordConfig);

        }
    }

    protected function registerEvents(): void
    {
        // region User Auth Activity
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
        // endregion User Auth Activity

        // region Content
        Event::listen(
            Events\Content\UpsertRoute::class,
            [Listeners\Content\ProcessContentRoute::class, 'upsert']
        );
        Event::listen(Events\Content\CreatingContentVersion::class, Listeners\Content\UnpubilshChildren::class);
        Event::listen(Events\Content\DispatchContentVersion::class, Listeners\Content\ProcessContentVersion::class);
        Event::listen(Events\Content\GenerateSitemap::class, Listeners\Content\GenerateContentSitemap::class);
        // endregion Content
    }

    protected function configureFilamentForm(): void
    {
        \Filament\Forms\Components\Field::macro('limitLengthWithHint', function (int | \Closure $length) {
            if ($this->isLive == null) {
                $this->lazy();
            }

            return $this
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
        // Model

        Support\Facades\ModelRegistry::replace(
            SupportModels\Contracts\MediaAsset::class,
            Facades\ModelManifest::get(SupportModels\Contracts\MediaAsset::class)
        );
        Support\Facades\ModelRegistry::replace(
            SupportModels\Contracts\NestableTree::class,
            Facades\ModelManifest::get(SupportModels\Contracts\NestableTree::class)
        );
        Support\Facades\ModelRegistry::setTablePrefix(InspireCmsConfig::get('models.table_name_prefix'));

        // Media Library

        Support\Facades\MediaLibraryRegistry::setDisk(InspireCmsConfig::get('media.media_library.disk', 'public'));
        Support\Facades\MediaLibraryRegistry::setDirectory(InspireCmsConfig::get('media.media_library.directory', ''));
        Support\Facades\MediaLibraryRegistry::setThumbnailCrop(InspireCmsConfig::get('media.media_library.thumbnail.width', 300), InspireCmsConfig::get('media.media_library.thumbnail.height', 300));
        Support\Facades\MediaLibraryRegistry::setShouldMapVideoPropertiesWithFfmpeg(boolval(InspireCmsConfig::get('media.media_library.should_map_video_properties_with_ffmpeg', false)));
        Support\Facades\MediaLibraryRegistry::setLimitedMimeTypes(InspireCmsConfig::get('media.media_library.allowed_mime_types', []));
        Support\Facades\MediaLibraryRegistry::setMaxSize(InspireCmsConfig::get('media.media_library.max_file_size', null));

        $mediaResponsive = InspireCmsConfig::get('media.media_library.responsive_images', []);
        if (is_array($mediaResponsive) && count($mediaResponsive) > 0) {
            foreach ($mediaResponsive as $name => $options) {
                if (is_null($options) || ! is_array($options) || ! is_string($name) || empty($name) || empty($options)) {
                    continue;
                }
                if (($options['enabled'] ?? false) !== true) {
                    continue;
                }
                if (! isset($options['width']) || ! is_int($options['width'])) {
                    continue;
                }
                Support\Facades\MediaLibraryRegistry::registerConversionUsing(
                    fn ($model, $media) => $model
                        ->addMediaConversion($name)
                        ->width($options['width'])
                        ->withResponsiveImages()
                        ->optimize()
                );
            }
        }

        // auth guard
        Support\Facades\AuthenticationManager::setAuthGuard(InspireCmsConfig::get('auth.guard.name'));

        // Resolvers

        foreach (InspireCmsConfig::get('resolvers', []) as $name => $resolver) {
            if (is_null($resolver)) {
                continue;
            }
            $interface = match ($name) {
                'user' => Support\Resolvers\UserResolverInterface::class,
                'published_content' => Resolvers\PublishedContentResolverInterface::class,
                default => null,
            };
            if (is_null($interface)) {
                $guessName = (string) str($name)->studly()->replace(' ', '')->append('ResolverInterface')->prepend('SolutionForest\\InspireCms\\Resolvers\\');
                if (! interface_exists($guessName)) {
                    $guessName = null;
                }
                $interface = $guessName;
            }
            Support\Facades\ResolverRegistry::set($interface, $resolver);
        }
        Support\Facades\ResolverRegistry::register($this->app);

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

        foreach (app(Filesystem::class)->allFiles(__DIR__ . '/../stubs/SampleAssets/Views/Themes') as $file) {

            $filename = TemplateHelper::ensureViewFileNameForTemplate(Str::kebab($file->getFilenameWithoutExtension()));
            $theme = Str::kebab($file->getRelativePath());

            if (($themeComponentDir = TemplateHelper::getDirectoryForThemedComponents()) && filled($themeComponentDir)) {
                $dir = $themeComponentDir . '/' . $theme;
            } else {
                $dir = base_path('resources/views/components/' . $theme);
            }

            $this->publishes([
                $file->getRealPath() => $dir . '/' . $filename,
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

    protected function registerComponentAndDirectives(): void
    {
        Blade::component('cms-template', ViewComponents\Template::class);

        Blade::directive('property', function ($expression) {

            $list = TemplateHelper::splitBladeExpressionForProperty($expression);

            if (count($list) === 4) {
                [$group, $property, $dtoVar, $propertyVarName] = $list;
            } else {
                // Return nothing
                return '';
            }

            return "<?php 
                \${$propertyVarName} = {$dtoVar}->getPropertyGroup('{$group}')?->getPropertyData('{$property}')?->getValue();
                echo is_array(\${$propertyVarName}) ? \\Illuminate\\Support\\Arr::first(\${$propertyVarName}) : \${$propertyVarName};
            ?>";
        });
        Blade::directive('propertyArray', function ($expression) {

            $list = TemplateHelper::splitBladeExpressionForProperty($expression);

            if (count($list) === 4) {
                [$group, $property, $dtoVar, $propertyVarName] = $list;
            } else {
                // Return nothing
                return '';
            }

            return "<?php 
                \${$propertyVarName} = {$dtoVar}->getPropertyGroup('{$group}')?->getPropertyData('{$property}')?->getValue();
            ?>";
        });

        Blade::directive('propertyNotEmpty', function ($expression) {

            $list = TemplateHelper::splitBladeExpressionForProperty($expression);

            if (count($list) === 4) {
                [$group, $property, $dtoVar, $propertyVarName] = $list;
            } else {
                // Return nothing
                return '';
            }

            return "<?php 
                \${$propertyVarName} = {$dtoVar}->getPropertyGroup('{$group}')?->getPropertyData('{$property}')?->getValue();
                if (\${$propertyVarName} != null && !empty(\${$propertyVarName})):
            ?>";
        });
    }

    private function addAboutPluginInfo()
    {
        AboutCommand::add('InspireCms', function () {

            $currentTheme = inspirecms_templates()->getCurrentTheme();

            return [
                'Version' => InspireCms::version(),
                'Theme' => filled($currentTheme)
                    ? "<fg=green;options=bold>{$currentTheme}</>"
                    : '<fg=yellow;options=bold>NOT SET</>',
            ];
        });
    }

    private static function configureFileFieldTypeConfigFormSchema(array $schema)
    {
        return array_map(function ($component) {

            if ($component instanceof \Filament\Forms\Components\Toggle
                && $component->getName() === 'multiple'
            ) {
                // Force the multiple toggle to be always on
                // and hidden from the form
                $component = $component
                    ->hidden()
                    ->dehydratedWhenHidden()
                    ->afterStateHydrated(function ($component) {
                        $component->state(true);
                    });
            }
            else if ($component instanceof \Filament\Forms\Components\Tabs
                || $component instanceof \Filament\Forms\Components\Tabs\Tab
            ) {
                $component->childComponents(static::configureFileFieldTypeConfigFormSchema($component->getChildComponents()));
            }

            return $component;
        }, $schema);
    }
}
