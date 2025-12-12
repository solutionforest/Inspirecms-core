<?php

namespace SolutionForest\InspireCmsApi;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use SolutionForest\InspireCmsApi\Http\Middleware\ApiAuthenticate;
use SolutionForest\InspireCmsApi\Http\Middleware\CheckApiAccess;
use SolutionForest\InspireCmsApi\Http\Middleware\CheckApiEnabled;
use SolutionForest\InspireCmsApi\Services\ApiRouteGenerator;
use SolutionForest\InspireCmsApi\Services\ApiSettingsService;
use SolutionForest\InspireCmsApi\Services\ContentQueryService;
use SolutionForest\InspireCmsApi\Services\FieldTransformerService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InspireCmsApiServiceProvider extends PackageServiceProvider
{
    public static string $name = 'inspirecms-api';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasMigrations([
                '2024_01_01_000001_create_cms_api_tokens_table',
                '2024_01_01_000002_add_api_settings_to_document_types',
                '2024_01_01_000003_add_api_settings_to_fields',
            ]);
    }

    public function packageRegistered(): void
    {
        // Register services
        $this->app->singleton(ApiSettingsService::class);
        $this->app->singleton(ApiRouteGenerator::class);
        $this->app->singleton(ContentQueryService::class);
        $this->app->singleton(FieldTransformerService::class);
    }

    public function packageBooted(): void
    {
        $this->configureRateLimiting();
        $this->registerRoutes();
        $this->registerMiddleware();
    }

    protected function configureRateLimiting(): void
    {
        if (! config('inspirecms-api.rate_limiting.enabled', true)) {
            return;
        }

        RateLimiter::for('inspirecms-api-public', function (Request $request) {
            return Limit::perMinute(config('inspirecms-api.rate_limiting.public', 60))
                ->by($request->ip());
        });

        RateLimiter::for('inspirecms-api-authenticated', function (Request $request) {
            return Limit::perMinute(config('inspirecms-api.rate_limiting.authenticated', 300))
                ->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function registerRoutes(): void
    {
        if (! config('inspirecms-api.enabled', true)) {
            return;
        }

        $prefix = config('inspirecms-api.prefix', 'api');
        $version = config('inspirecms-api.version', 'v1');

        Route::group([
            'prefix' => "{$prefix}/{$version}",
            'middleware' => ['api', CheckApiEnabled::class],
            'as' => 'inspirecms.api.',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('inspirecms.api.auth', ApiAuthenticate::class);
        $this->app['router']->aliasMiddleware('inspirecms.api.access', CheckApiAccess::class);
        $this->app['router']->aliasMiddleware('inspirecms.api.enabled', CheckApiEnabled::class);
    }
}
