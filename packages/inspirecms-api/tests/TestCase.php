<?php

namespace SolutionForest\InspireCmsApi\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use SolutionForest\InspireCms\InspireCmsServiceProvider;
use SolutionForest\InspireCmsApi\InspireCmsApiServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function getPackageProviders($app): array
    {
        return [
            InspireCmsServiceProvider::class,
            InspireCmsApiServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Configure test database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure API settings
        $app['config']->set('inspirecms-api.enabled', true);
        $app['config']->set('inspirecms-api.prefix', 'api');
        $app['config']->set('inspirecms-api.version', 'v1');
        $app['config']->set('inspirecms-api.rate_limiting.enabled', false);

        // Configure InspireCMS
        $app['config']->set('inspirecms.models.table_name_prefix', 'cms_');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
