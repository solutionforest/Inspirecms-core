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
use SolutionForest\InspireCms\InspireCmsServiceProvider;
use SolutionForest\InspireCms\Support\InspireCmsSupportServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

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

            FilamentFieldGroupServiceProvider::class,
            \Kalnoy\Nestedset\NestedSetServiceProvider::class,

            InspireCmsSupportServiceProvider::class,

            InspireCmsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        //region inspirecms
        static::registerTestModels();
        //endregion inspirecms

        //region inspirecms support
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest::setDisk(config('inspirecms.media_library.disk'));
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest::setDirectory(config('inspirecms.media_library.directory'));
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest::setThumbnailCrop(config('inspirecms.media_library.thumbnail.width'), config('inspirecms.media_library.thumbnail.height'));

        \SolutionForest\InspireCms\Support\Facades\InspireCmsSupport::setTablePrefix(config('inspirecms.models.table_name_prefix'));

        \SolutionForest\InspireCms\Support\Facades\ResolverManifest::set('user', config('inspirecms.resolvers.user', \SolutionForest\InspireCms\Support\Resolver\UserResolver::class));
        //endregion inspirecms support

        $migrations = [
            __DIR__ . '/../database/migrations/create_inspire-cms-core_table.php.stub',
            __DIR__ . '/../vendor/solution-forest/inspirecms-support/database/migrations/create_nestable-trees_table.php.stub',
            __DIR__ . '/../vendor/solution-forest/inspirecms-support/database/migrations/create_media-assets_table.php.stub',
            __DIR__ . '/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub',
        ];

        foreach ($migrations as $migrationPath) {
            $migration = include $migrationPath;
            $migration->up();
        }
    }

    protected static function registerTestModels()
    {
        foreach (array_keys(config('inspirecms.models.fqcn')) as $key) {

            $guessName = (string) str($key)->studly()->replace(' ', '');

            $testModel = "SolutionForest\\InspireCms\\Tests\\TestModels\\{$guessName}";

            if (in_array($key, ['media_asset', 'nestable_tree'])) {
                $testModel = "SolutionForest\\InspireCms\\Support\\Tests\\TestModels\\{$guessName}";
            }

            if (! class_exists($testModel)) {
                continue;
            }

            config()->set("inspirecms.models.fqcn.{$key}", $testModel);
        }

        \SolutionForest\InspireCms\Facades\ModelManifest::register();
    }

    protected function getTable($table)
    {
        if ($table instanceof \Illuminate\Database\Eloquent\Model || is_string($table) && class_exists($table)) {
            return parent::getTable($table);
        }

        return config('inspirecms.models.table_name_prefix') . $table;
    }
}
