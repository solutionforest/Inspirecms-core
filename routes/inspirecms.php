<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use SolutionForest\InspireCms\Http\Controllers\ImportController;
use SolutionForest\InspireCms\Http\Middleware\CmsAuthenticate;
use SolutionForest\InspireCms\Http\Middleware\CmsAuthenticateSession;
use SolutionForest\InspireCms\Http\Middleware\SetCmsPanel;

Route::name('inspirecms.')->prefix('/inspirecms')->group(function () {

    $getBaseMiddlewares = fn ($haveAuth = true) => array_filter([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        $haveAuth ? CmsAuthenticateSession::class : null,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        SubstituteBindings::class,
    ]);

    $authMiddleware = [
        ...$getBaseMiddlewares(),
        SetCmsPanel::class,
        CmsAuthenticate::class,
    ];

    // Auth check
    Route::middleware($authMiddleware)->group(function () {
        Route::name('import.')->prefix('import')->group(function () {
            Route::get('sample', [ImportController::class, 'sample'])->name('sample');
        });
    });
});
