<?php

namespace SolutionForest\InspireCms\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use SolutionForest\FilamentFieldGroup\FilamentFieldGroupServiceProvider;
use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\InspireCmsServiceProvider;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\InspireCmsSupportServiceProvider;
use SolutionForest\InspireCms\Tests\Models\User;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => '\\SolutionForest\\InspireCms\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
        Factory::guessModelNamesUsing(
            fn ($factory) => 'SolutionForest\\InspireCms\\Tests\\Models\\' . str_replace('Factory', '', class_basename($factory))
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,

            \Spatie\Permission\PermissionServiceProvider::class,

            FilamentFieldGroupServiceProvider::class,
            \Kalnoy\Nestedset\NestedSetServiceProvider::class,

            \Pboivin\FilamentPeek\FilamentPeekServiceProvider::class,

            InspireCmsSupportServiceProvider::class,

            InspireCmsServiceProvider::class,

            \SolutionForest\InspireCms\CmsPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('app.key', 'base64:I4ofV4eI4v12PUp+g9ZahXUu0ZhPCbk1Q8iawecCtdw=');

        $this->registerTestModels($app);

        $app['config']->set('auth.providers.inspirecms', [
            'driver' => 'eloquent',
            'model' => $app['config']->get('inspirecms.models.fqcn.user'),
        ]);
        $app['config']->set('auth.guards.inspirecms', [
            'driver' => 'session',
            'provider' => InspireCmsConfig::getGuardName(),
        ]);

        // Extra resources
        $app['config']->set('inspirecms.filament.resources.custom_post', \SolutionForest\InspireCms\Tests\Fixtures\Filament\Resources\PostResource::class);

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
        // End with '/../database/migrations'
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
        // plugin migrations
        $this->loadMigrationsFrom([
            __DIR__ . '/../../vendor/solution-forest/inspirecms-support/database/migrations',
            // __DIR__ . '/../../vendor/spatie/laravel-permission/database/migrations',
            // __DIR__ . '/../../vendor/spatie/laravel-medialibrary/database/migrations',

            __DIR__ . '/../../database/migrations',
        ]);

        // test migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->artisan('migrate', ['--database' => 'testbench'])->run();

    }

    /** {@inheritDoc} */
    protected function destroyDatabaseMigrations()
    {
        //
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
        ]);

        $user->syncRoles($role);
    }

    public function loginCmsPanelAsSuperAdmin()
    {
        $user = User::first();

        return $this->actingAs($user, InspireCmsConfig::getGuardName());
    }
}
