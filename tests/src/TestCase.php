<?php

namespace SolutionForest\InspireCms\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Tests\Models\Content;
use SolutionForest\InspireCms\Tests\Models\DocumentType;
use SolutionForest\InspireCms\Tests\Models\Field;
use SolutionForest\InspireCms\Tests\Models\FieldGroup;
use SolutionForest\InspireCms\Tests\Models\KeyValue;
use SolutionForest\InspireCms\Tests\Models\Language;
use SolutionForest\InspireCms\Tests\Models\Template;
use SolutionForest\InspireCms\Tests\Models\User;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => '\\SolutionForest\\InspireCms\\Tests\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
        Factory::guessModelNamesUsing(
            fn ($factory) => 'SolutionForest\\InspireCms\\Tests\\Models\\' . str_replace('Factory', '', class_basename($factory))
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
            \BladeUI\Icons\BladeIconsServiceProvider::class,

            \Filament\Actions\ActionsServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Infolists\InfolistsServiceProvider::class,
            \Filament\Notifications\NotificationsServiceProvider::class,
            \Filament\Schemas\SchemasServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Tables\TablesServiceProvider::class,
            \Filament\Widgets\WidgetsServiceProvider::class,

            \Kalnoy\Nestedset\NestedSetServiceProvider::class,
            \Khatabwedaa\BladeCssIcons\BladeCssIconsServiceProvider::class,
            \Kirschbaum\PowerJoins\PowerJoinsServiceProvider::class,

            \Livewire\LivewireServiceProvider::class,

            \Pboivin\FilamentPeek\FilamentPeekServiceProvider::class,
            \RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider::class,

            \SolutionForest\FilamentFieldGroup\FilamentFieldGroupServiceProvider::class,
            \SolutionForest\InspireCms\CmsPanelProvider::class,
            \SolutionForest\InspireCms\InspireCmsServiceProvider::class,
            \SolutionForest\InspireCms\Support\InspireCmsSupportServiceProvider::class,

            \Spatie\Permission\PermissionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('app.key', 'base64:I4ofV4eI4v12PUp+g9ZahXUu0ZhPCbk1Q8iawecCtdw=');

        // Avoid migration for 'cache', 'sessions', to avoid migration error
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');

        $this->registerTestModels($app);

        $app['config']->set('auth.providers.inspirecms', [
            'driver' => 'eloquent',
            'model' => $app['config']->get('inspirecms.models.fqcn.user'),
        ]);
        $app['config']->set('auth.guards.inspirecms', [
            'driver' => 'session',
            'provider' => AuthHelper::guardName(),
        ]);

        // Extra resources
        $app['config']->set('inspirecms.admin.resources.custom_post', \SolutionForest\InspireCms\Tests\Fixtures\Filament\Resources\PostResource::class);

        ModelManifest::register();
        ModelManifest::registerMorphMap();

    }

    protected function registerTestModels($app)
    {
        foreach (array_keys($app['config']->get('inspirecms.models.fqcn')) as $key) {

            $guessName = (string) str($key)->studly()->replace(' ', '');

            $testModel = "SolutionForest\\InspireCms\\Tests\\Models\\{$guessName}";

            // models from inspirecms-support
            if (in_array($key, ['media_asset', 'nestable_tree'])) {

                $testModel = "SolutionForest\\InspireCms\\Support\\Tests\\Models\\{$guessName}";

                ModelRegistry::replace(
                    "SolutionForest\\InspireCms\\Support\\Models\\Contracts\\{$guessName}",
                    $testModel
                );
                include_once __DIR__ . '/../../vendor/solution-forest/inspirecms-support/tests/src/Models/' . $guessName . '.php';
            }

            $app['config']->set("inspirecms.models.fqcn.{$key}", $testModel);
        }
    }

    protected function loadMigrationsFrom($paths): void
    {
        // Stub files
        if (is_array($paths)) {

            foreach ($paths as $folder) {

                $migrationPath = realpath($folder);

                if ($migrationPath == false) {
                    continue;
                }

                // Load .stub files
                foreach (glob("{$migrationPath}/*.php.stub") as $path) {

                    $migration = include $path;
                    $migration->up();
                }
            }

        }
        // Is tests migrations
        elseif (is_string($paths) && str($paths)->endsWith('/../database/migrations')) {
            $migrationPath = realpath(__DIR__ . '/../database/migrations');

            foreach (glob("{$migrationPath}/*.php") as $path) {

                $migration = include $path;
                $migration->up();
            }
        } else {
            parent::loadMigrationsFrom($paths);
        }
    }

    /** {@inheritDoc} */
    protected function defineDatabaseMigrations()
    {
        // $this->loadLaravelMigrations();

        // plugin migrations
        $this->loadMigrationsFrom([
            __DIR__ . '/../../vendor/solution-forest/filament-field-group/database/migrations',
            __DIR__ . '/../../vendor/solution-forest/inspirecms-support/database/migrations',
            // __DIR__ . '/../../vendor/spatie/laravel-permission/database/migrations',
            // __DIR__ . '/../../vendor/spatie/laravel-medialibrary/database/migrations',

            __DIR__ . '/../../database/migrations',
        ]);

        // test migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    protected function getTable($table)
    {
        if ($table instanceof \Illuminate\Database\Eloquent\Model || is_string($table) && class_exists($table)) {
            return parent::getTable($table);
        }

        return InspireCmsConfig::get('models.table_name_prefix') . $table;
    }

    protected function createSuperAdminUser()
    {
        $role = PermissionHelper::setupSuperAdminRole();

        /**
         * @var User
         */
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => \Hash::make('password'), // Change this to a secure password
            'preferred_language' => 'en',
            'uuid' => (string) \Str::uuid7(),
        ]);

        $user->syncRoles($role);

        return $user;
    }

    public function loginCmsPanelAsSuperAdmin()
    {
        $user = User::first();

        return $this->actingAs($user, AuthHelper::guardName());
    }

    public function registerCmsRoutes()
    {
        inspirecms()->routes();

        return $this;
    }

    public function ensureDefaultTheme()
    {
        return KeyValue::updateOrCreate(
            ['key' => TemplateHelper::getCurrentThemeKey()],
            ['value' => 'default']
        );
    }

    public function ensureDefaultLanguage()
    {
        $defaultLangCode = 'en';

        return Language::updateOrCreate(
            ['code' => $defaultLangCode],
            ['is_default' => true]
        );
    }

    public function addCmsContentVersion($content, array $data = [], array $publishableData = [], ?string $publishState = 'publish')
    {
        if (! empty($data)) {
            $content->propertyData = json_encode($data);
        }
        if ($publishState) {
            $content->setPublishableState($publishState);
        }
        if (! empty($publishableData)) {
            $content->setPublishableData($publishableData);
        }
        $content->save();
        $content->refresh();

        return $content;
    }

    public function createCmsContent(array $data = [], array $propData = [], array $publishableData = [], ?string $publishState = 'draft')
    {
        $facDocumentType = DocumentType::factory(['category' => 'web'])
            ->hasAttached(
                Template::factory([
                    'content' => [
                        $this->ensureDefaultTheme()->value => <<<'HTML'
                            <div class="content">
                                <p>Test</p>
                            </div>
                        HTML,
                    ],
                ]),
                ['is_default' => true]
            );

        foreach ($propData as $key => $value) {
            if (! is_array($value)) {
                continue;
            }
            $facFieldGroup = FieldGroup::factory(['name' => $key]);
            foreach (array_keys($value) as $fieldKey) {
                $facFieldGroup = $facFieldGroup->has(Field::factory(['name' => $fieldKey, 'type' => 'text']));
            }
            $facDocumentType = $facDocumentType->has($facFieldGroup);
        }

        $facContent = Content::factory($data)->for($facDocumentType);
        if (! empty($propData)) {
            $facContent = $facContent->havePropertyData($propData);
        }

        $content = $facContent->make();
        if ($publishState) {
            $content->setPublishableState($publishState);
        }
        if (! empty($publishableData)) {
            $content->setPublishableData($publishableData);
        }
        $content->save();
        $content->refresh();

        return $content;
    }
}
