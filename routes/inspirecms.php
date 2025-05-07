<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use SolutionForest\InspireCms\Http\Controllers;
use SolutionForest\InspireCms\Http\Middleware as CmsMiddleware;
use SolutionForest\InspireCms\InspireCmsConfig;

Route::name('inspirecms.')->group(function () {

    $getBaseMiddlewares = fn ($haveAuth = true) => array_filter([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        $haveAuth ? CmsMiddleware\CmsAuthenticateSession::class : null,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        SubstituteBindings::class,
        CmsMiddleware\SetUpPoweredBy::class,
    ]);

    Route::prefix('/inspirecms')
        ->middleware([
            ...$getBaseMiddlewares(),
            CmsMiddleware\SetUpCmsPanel::class,
            CmsMiddleware\CmsAuthenticate::class,
        ])
        ->group(function () {
            Route::name('import.')
                ->prefix('import')
                ->group(function () {
                    Route::name('sample')->get('sample', [Controllers\ImportController::class, 'sample']);
                });
        });

    Route::name('asset')
        ->get('assets/{key}', Controllers\AssetController::class)
        ->middleware(InspireCmsConfig::get('media.media_library.middleware', [
            CmsMiddleware\SetUpPoweredBy::class,
            'cache.headers:public;max_age=2628000;etag',
        ]));
});
