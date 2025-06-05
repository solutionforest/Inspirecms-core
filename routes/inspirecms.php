<?php

use Illuminate\Support\Facades\Route;
use SolutionForest\InspireCms\Http\Controllers;
use SolutionForest\InspireCms\Http\Middleware as CmsMiddleware;
use SolutionForest\InspireCms\InspireCmsConfig;

Route::name('inspirecms.')->group(function () {

    Route::name('asset')
        ->get('assets/{key}', Controllers\AssetController::class)
        ->middleware(InspireCmsConfig::get('media.media_library.middleware', [
            CmsMiddleware\SetUpPoweredBy::class,
            'cache.headers:public;max_age=2628000;etag',
        ]));
});
