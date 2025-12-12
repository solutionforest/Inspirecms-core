<?php

namespace SolutionForest\InspireCms;

use Closure;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Theme;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Events as AuthEvents;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\File;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Image;
use SolutionForest\InspireCms\Base as InspireCmsBase;
use SolutionForest\InspireCms\Base\Manifests as BaseManifests;
use SolutionForest\InspireCms\Commands\CacheStatsCommand;
use SolutionForest\InspireCms\Commands\ClearCacheCommand;
use SolutionForest\InspireCms\Commands\DataCleanupCommand;
use SolutionForest\InspireCms\Commands\ExecuteExportCommand;
use SolutionForest\InspireCms\Commands\ExecuteImportCommand;
use SolutionForest\InspireCms\Commands\GenerateSitemapCommand;
use SolutionForest\InspireCms\Commands\ImportDefaultDataCommand;
use SolutionForest\InspireCms\Commands\InstallCommand;
use SolutionForest\InspireCms\Commands\InstallRequirePacakgesCommand;
use SolutionForest\InspireCms\Commands\PublishPanelCommand;
use SolutionForest\InspireCms\Commands\RepairPermissionsCommand;
use SolutionForest\InspireCms\Commands\RoutesCommand;
use SolutionForest\InspireCms\Commands\UpdateCommand;
use SolutionForest\InspireCms\Events\Content\CreatingContentVersion;
use SolutionForest\InspireCms\Events\Content\DispatchContentVersion;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
use SolutionForest\InspireCms\Events\Content\UpsertRoute;
use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Factories\PreviewFactory;
use SolutionForest\InspireCms\Fields\Mixins\FieldTypeDefinition;
use SolutionForest\InspireCms\Fields\PropertyValueTransformer;
use SolutionForest\InspireCms\Fields\PropertyValueTransformerInterface;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\Http\Middleware\CmsAuthenticate;
use SolutionForest\InspireCms\Http\Responses\Auth\RegistrationResponse;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use SolutionForest\InspireCms\Listeners\Content\GenerateContentSitemap;
use SolutionForest\InspireCms\Listeners\Content\ProcessContentRoute;
use SolutionForest\InspireCms\Listeners\Content\ProcessContentVersion;
use SolutionForest\InspireCms\Listeners\Content\UnpubilshChildren;
use SolutionForest\InspireCms\Listeners\UserAuthActivityListener;
use SolutionForest\InspireCms\Livewire\ContentSidebar;
use SolutionForest\InspireCms\Livewire\ContentTreeNode;
use SolutionForest\InspireCms\Livewire\ContentVersionHistory;
use SolutionForest\InspireCms\Livewire\TableReleatedLivewireComponent;
use SolutionForest\InspireCms\Models\Contracts\User;
use SolutionForest\InspireCms\Models\Field;
use SolutionForest\InspireCms\Models\FieldGroup;
use SolutionForest\InspireCms\Resolvers\PublishedContentResolverInterface;
use SolutionForest\InspireCms\Services\AssetService;
use SolutionForest\InspireCms\Services\AssetServiceInterface;
use SolutionForest\InspireCms\Services\ContentService;
use SolutionForest\InspireCms\Services\ContentServiceInterface;
use SolutionForest\InspireCms\Services\ExportService;
use SolutionForest\InspireCms\Services\ExportServiceInterface;
use SolutionForest\InspireCms\Services\ImportDataService;
use SolutionForest\InspireCms\Services\ImportDataServiceInterface;
use SolutionForest\InspireCms\Services\ImportService;
use SolutionForest\InspireCms\Services\ImportServiceInterface;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Facades\ResolverRegistry;
use SolutionForest\InspireCms\Support\InspireCmsSupportServiceProvider;
use SolutionForest\InspireCms\Support\Models as SupportModels;
use SolutionForest\InspireCms\Support\Resolvers\UserResolverInterface;
use SolutionForest\InspireCms\Testing\TestsInspireCms;
use SolutionForest\InspireCms\View\Components as ViewComponents;
use SolutionForest\InspireCms\VisualEditor\VisualEditorServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Throwable;

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
            ->hasCommands($this->getCommands());

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
        $this->app->register(InspireCmsSupportServiceProvider::class);

        // Register Visual Editor if enabled
        if (InspireCmsConfig::get('visual_editor.enabled', true)) {
            $this->app->register(VisualEditorServiceProvider::class);
        }
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

        $this->app->singleton(AssetServiceInterface::class, fn () => $this->app->make(AssetService::class));
        $this->app->singleton(ContentServiceInterface::class, fn () => $this->app->make(ContentService::class));
        $this->app->singleton(ImportDataServiceInterface::class, fn () => $this->app->make(ImportDataService::class));
        $this->app->singleton(ImportServiceInterface::class, fn () => $this->app->make(ImportService::class));
        $this->app->singleton(ExportServiceInterface::class, fn () => $this->app->make(ExportService::class));

        $this->app->singleton(PropertyValueTransformerInterface::class, PropertyValueTransformer::class);

        FieldTypeBaseConfig::mixin(new FieldTypeDefinition);

        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\RegistrationResponse::class, RegistrationResponse::class);

        $this->registerModels();

        $this->customPlugins();

        $this->registerSupport();

        $this->registerAuthGuard();

    }

    public function bootingPackage(): void
    {
        ModelManifest::registerMorphMap();
        ModelManifest::registerPolices();

        $this->registerComponentAndDirectives();

        $this->registerEvents();

        $this->registerScheduleCommands();
    }

    public function packageBooted(): void
    {
        $this->configureFilamentForm();

        Livewire::component(TableReleatedLivewireComponent::class);
        Livewire::component('inspirecms::content-version-history', ContentVersionHistory::class);
        Livewire::component('inspirecms::content-sidebar', ContentSidebar::class);
        Livewire::component('inspirecms::content-tree-node', ContentTreeNode::class);

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        Livewire::addPersistentMiddleware([
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

            Js::make('rich-content-plugins/extension-cms-content-link', __DIR__ . '/../resources/dist/components/rich-content-plugins/extension-cms-content-link.js')->loadedOnRequest(),
            Js::make('rich-content-plugins/extension-cms-media-link', __DIR__ . '/../resources/dist/components/rich-content-plugins/extension-cms-media-link.js')->loadedOnRequest(),

            Css::make('filament-code-editor', __DIR__ . '/../resources/dist/components/code-editor.css')->loadedOnRequest(),
            AlpineComponent::make('filament-code-editor', __DIR__ . '/../resources/dist/components/code-editor.js')->loadedOnRequest(),
            AlpineComponent::make('markdown-editor', __DIR__ . '/../resources/dist/components/markdown-editor.js')->loadedOnRequest(),
            Css::make('filament-alert', __DIR__ . '/../resources/dist/components/alert.css')->loadedOnRequest(),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            InstallCommand::class,
            Commands\AboutCommand::class,
            CacheStatsCommand::class,
            GenerateSitemapCommand::class,
            UpdateCommand::class,
            PublishPanelCommand::class,
            InstallRequirePacakgesCommand::class,
            ImportDefaultDataCommand::class,
            ExecuteImportCommand::class,
            ExecuteExportCommand::class,
            DataCleanupCommand::class,
            RepairPermissionsCommand::class,
            RoutesCommand::class,
            ClearCacheCommand::class,
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
            'fields' => 'css-edit-flip-h',
            'templates' => 'css-template',
            'document_type' => 'css-collage',

            'content_picker' => view('inspirecms::icons.content-picker'),
            'media_picker' => view('inspirecms::icons.media-picker'),
            'icon_picker' => view('inspirecms::icons.icon-picker'),

        ])->mapWithKeys(fn ($icon, $key) => ["{$iconPrefix}{$key}" => $icon])->all();
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [
            //
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
            'create_custom_spatie_permission_table', // uuid enhac
            'update_sessions_table', // uuid enhance
            'update_inspirecms-cms-users_table',
            'hotfix_inspirecms_users_table',
            'create_visual_editor_tables', // visual editor tables
        ];
    }

    protected function registerModels(): void
    {
        ModelManifest::register();

        $supportModels = $this->getConfigSupoortModels();
        ModelManifest::replace(
            SupportModels\Contracts\MediaAsset::class,
            $supportModels['media_asset']
        );
        ModelManifest::replace(
            SupportModels\Contracts\NestableTree::class,
            $supportModels['nestable_tree']
        );
    }

    protected function customPlugins(): void
    {
        if (InspireCmsConfig::get('system.override_plugins.field_group_models', true)) {

            // override field group models
            FilamentFieldGroup::setFieldGroupModelClass(
                FieldGroup::class
            );
            FilamentFieldGroup::setFieldModelClass(
                Field::class
            );

            // customizing the config form schema
            FilamentFieldGroup::configureFieldTypeConfigFormUsing(
                File::class,
                fn ($field, array $schema) => static::configureFileFieldTypeConfigFormSchema($schema)
            );
            FilamentFieldGroup::configureFieldTypeConfigFormUsing(
                Image::class,
                fn ($field, array $schema) => static::configureFileFieldTypeConfigFormSchema($schema)
            );
        }

        if (InspireCmsConfig::get('system.override_plugins.spatie_permission', true)) {
            config()->set('permission.enable_wildcard_permission', true);
        }

        if (InspireCmsConfig::get('system.override_plugins.filament_peek', true)) {
            PreviewFactory::create()->configureFilamentPeekAsInternalLink();
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
                'model' => ModelManifest::get(
                    User::class,
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
            [UserAuthActivityListener::class, 'login']
        );
        Event::listen(
            AuthEvents\Logout::class,
            [UserAuthActivityListener::class, 'logout']
        );
        Event::listen(
            AuthEvents\Failed::class,
            [UserAuthActivityListener::class, 'loginFailed']
        );
        Event::listen(
            AuthEvents\PasswordReset::class,
            [UserAuthActivityListener::class, 'passwordReset']
        );
        // endregion User Auth Activity

        // region Content
        Event::listen(
            UpsertRoute::class,
            [ProcessContentRoute::class, 'upsert']
        );
        Event::listen(CreatingContentVersion::class, UnpubilshChildren::class);
        Event::listen(DispatchContentVersion::class, ProcessContentVersion::class);
        Event::listen(GenerateSitemap::class, GenerateContentSitemap::class);
        // endregion Content
    }

    protected function configureFilamentForm(): void
    {
        \Filament\Forms\Components\Field::macro('limitLengthWithHint', function (int | Closure $length) {
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

        ModelRegistry::replace(
            SupportModels\Contracts\MediaAsset::class,
            ModelManifest::get(SupportModels\Contracts\MediaAsset::class)
        );
        ModelRegistry::replace(
            SupportModels\Contracts\NestableTree::class,
            ModelManifest::get(SupportModels\Contracts\NestableTree::class)
        );
        ModelRegistry::setTablePrefix(InspireCmsConfig::get('models.table_name_prefix'));

        // Media Library

        MediaLibraryRegistry::setDisk(InspireCmsConfig::get('media.media_library.disk', 'public'));
        MediaLibraryRegistry::setThumbnailCrop(InspireCmsConfig::get('media.media_library.thumbnail.width', 300), InspireCmsConfig::get('media.media_library.thumbnail.height', 300));
        MediaLibraryRegistry::setShouldMapVideoPropertiesWithFfmpeg(boolval(InspireCmsConfig::get('media.media_library.should_map_video_properties_with_ffmpeg', false)));
        MediaLibraryRegistry::setLimitedMimeTypes(InspireCmsConfig::get('media.media_library.allowed_mime_types', []));
        MediaLibraryRegistry::setMaxSize(InspireCmsConfig::get('media.media_library.max_file_size', null));

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
                MediaLibraryRegistry::registerConversionUsing(
                    fn ($model, $media) => $model
                        ->addMediaConversion($name)
                        ->width($options['width'])
                        ->withResponsiveImages()
                        ->optimize()
                );
            }
        }

        // Resolvers

        foreach (InspireCmsConfig::get('resolvers', []) as $name => $resolver) {
            if (is_null($resolver)) {
                continue;
            }
            $interface = match ($name) {
                'user' => UserResolverInterface::class,
                'published_content' => PublishedContentResolverInterface::class,
                default => null,
            };
            if (is_null($interface)) {
                $guessName = (string) str($name)->studly()->replace(' ', '')->append('ResolverInterface')->prepend('SolutionForest\\InspireCms\\Resolvers\\');
                if (! interface_exists($guessName)) {
                    $guessName = null;
                }
                $interface = $guessName;
            }
            ResolverRegistry::set($interface, $resolver);
        }
        ResolverRegistry::register($this->app);

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
        $schedule = $this->app[Schedule::class];

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

            } catch (Throwable $th) {
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
            $dir = str($file->getRelativePath())
                ->replace('\\', '/')
                ->explode('/')
                ->filter(fn ($path) => filled($path))
                ->map(fn ($path) => Str::kebab($path))
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

            if ($component instanceof Toggle
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
            } elseif ($component instanceof Tabs
                || $component instanceof Tab
            ) {
                $component->childComponents(static::configureFileFieldTypeConfigFormSchema($component->getDefaultChildComponents()));
            }

            return $component;
        }, $schema);
    }
}
