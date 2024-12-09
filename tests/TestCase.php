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
use Illuminate\Database\Schema\Blueprint;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use SolutionForest\FilamentFieldGroup\FilamentFieldGroupServiceProvider;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\InspireCmsServiceProvider;
use SolutionForest\InspireCms\Support\InspireCmsSupportServiceProvider;
use SolutionForest\InspireCms\Tests\TestModels\User;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'SolutionForest\\InspireCms\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
        Factory::guessModelNamesUsing(
            fn ($factory) => 'SolutionForest\\InspireCms\\Tests\\TestModels\\' . str_replace('Factory', '', class_basename($factory))
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

        //region inspirecms
        static::registerTestModels();

        // Extra resources
        $app['config']->set('inspirecms.filament.resources.custom_post', \SolutionForest\InspireCms\Tests\Support\Filament\Resources\PostResource::class);

        //endregion inspirecms

        //region inspirecms support
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry::setDisk(InspireCmsConfig::get('media_library.disk'));
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry::setDirectory(InspireCmsConfig::get('media_library.directory'));
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry::setThumbnailCrop(InspireCmsConfig::get('media_library.thumbnail.width'), InspireCmsConfig::get('media_library.thumbnail.height'));
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry::setShouldMapVideoPropertiesWithFfmpeg(boolval(InspireCmsConfig::get('media_library.should_map_video_properties_with_ffmpeg', false)));

        \SolutionForest\InspireCms\Support\Facades\InspireCmsSupport::setTablePrefix(InspireCmsConfig::get('models.table_name_prefix'));
        \SolutionForest\InspireCms\Support\Facades\InspireCmsSupport::setAuthGuard(InspireCmsConfig::get('auth.guard'));

        \SolutionForest\InspireCms\Support\Facades\ResolverRegistry::set('user', InspireCmsConfig::get('resolvers.user', \SolutionForest\InspireCms\Support\Resolvers\UserResolver::class));
        //endregion inspirecms support

        $migrations = [
            __DIR__ . '/../database/migrations/create_inspire-cms-core_table.php.stub',
            __DIR__ . '/../vendor/solution-forest/inspirecms-support/database/migrations/create_nestable-trees_table.php.stub',
            __DIR__ . '/../vendor/solution-forest/inspirecms-support/database/migrations/create_media-assets_table.php.stub',
            __DIR__ . '/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub',
            __DIR__ . '/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub',
        ];

        foreach ($migrations as $migrationPath) {
            $migration = include $migrationPath;
            $migration->up();
        }
    }

    protected static function registerTestModels()
    {
        foreach (array_keys(InspireCmsConfig::get('models.fqcn')) as $key) {

            $guessName = (string) str($key)->studly()->replace(' ', '');

            $testModel = "SolutionForest\\InspireCms\\Tests\\TestModels\\{$guessName}";

            // Support plugin's models
            if (in_array($key, ['media_asset', 'nestable_tree'])) {
                $testModel = "SolutionForest\\InspireCms\\Support\\Tests\\TestModels\\{$guessName}";
                \SolutionForest\InspireCms\Support\Facades\ModelRegistry::replace("SolutionForest\\InspireCms\\Support\\Models\\Contracts\\{$guessName}", $testModel);
            }

            if (! class_exists($testModel)) {
                continue;
            }

            config()->set("inspirecms.models.fqcn.{$key}", $testModel);
        }

        \SolutionForest\InspireCms\Facades\ModelManifest::register();

        \SolutionForest\InspireCms\Facades\ModelManifest::registerMorphMap();
    }

    protected function getTable($table)
    {
        if ($table instanceof \Illuminate\Database\Eloquent\Model || is_string($table) && class_exists($table)) {
            return parent::getTable($table);
        }

        return InspireCmsConfig::get('models.table_name_prefix') . $table;
    }

    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        $schema->create('test_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
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
