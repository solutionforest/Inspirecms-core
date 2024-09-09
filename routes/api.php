<?php

use Illuminate\Support\Facades\Route;
use SolutionForest\InspireCms\Livewire\Components\PreviewContent;

Route::prefix('api')->group(function () {
    Route::name('preview.')
        ->group(function () {
            Route::get('/preview-content/{content}', PreviewContent::class)->name('content');
        });
});