<?php

use Illuminate\Support\Facades\Route;
use SolutionForest\InspireCms\Http\Controllers\ImportController;
use SolutionForest\InspireCms\InspireCmsConfig;

Route::prefix(config('insiprecms.filament.path', 'cms'))->group(function () {

    // Redirect to the login page if the user is not authenticated for session authentication
    Route::get('/login-redirect', function () {

        $url = (filament()->getCurrentPanel() ?? filament()->getPanel(config('insiprecms.filament.panel_id', 'cms')))
            ?->getLoginUrl() ?? ('/' . config('insiprecms.filament.path', 'cms') . '/login');

        return redirect()->intended($url);

    })->name('login');

    Route::name('cms.')->middleware(['web', 'auth:' . InspireCmsConfig::getGuardName(), 'verified'])->group(function () {
        Route::name('import.')->prefix('import')->group(function () {
            Route::get('sample', [ImportController::class, 'sample'])->name('sample');
        });
    });
});
