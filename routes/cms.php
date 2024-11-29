<?php

use Illuminate\Support\Facades\Route;

Route::prefix(config('insiprecms.filament.path', 'cms'))->group(function () {

    // Redirect to the login page if the user is not authenticated for session authentication
    Route::get('/login-redirect', function () {

        $url = (filament()->getCurrentPanel() ?? filament()->getPanel(config('insiprecms.filament.panel_id', 'cms')))
            ?->getLoginUrl() ?? ('/' . config('insiprecms.filament.path', 'cms') . '/login');

        return redirect()->intended($url);

    })->name('login');

    // Donwload the samples
    Route::name('cms.samples.')->prefix('samples')->group(function () {

        Route::get('import-job/download', function () {
            
            return response()->download(public_path('vendor/inspirecms/sample/import-job-sample.zip'));

        })->name('download-import-job');

    });
});
