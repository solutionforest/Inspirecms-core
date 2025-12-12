<?php

use Illuminate\Support\Facades\Route;
use SolutionForest\InspireCmsApi\Http\Controllers\Api\ContentTypeController;
use SolutionForest\InspireCmsApi\Http\Controllers\Api\SchemaController;
use SolutionForest\InspireCmsApi\Http\Controllers\Api\TokenController;

/*
|--------------------------------------------------------------------------
| InspireCMS API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the InspireCmsApiServiceProvider and are
| prefixed with /api/v1 (configurable).
|
*/

// Schema / Discovery endpoints (public)
Route::get('schema', [SchemaController::class, 'index'])->name('schema.index');
Route::get('schema/{type}', [SchemaController::class, 'show'])->name('schema.show');

// Authentication endpoints
Route::prefix('auth')->group(function () {
    // Create token (login)
    Route::post('token', [TokenController::class, 'store'])->name('auth.token.store');

    // Protected token management routes
    Route::middleware('inspirecms.api.auth')->group(function () {
        Route::delete('token', [TokenController::class, 'destroy'])->name('auth.token.destroy');
        Route::get('tokens', [TokenController::class, 'index'])->name('auth.tokens.index');
        Route::delete('tokens/{id}', [TokenController::class, 'revokeById'])->name('auth.tokens.revoke');
    });
});

// Dynamic content type routes
// These routes handle all content types based on their API slug

// Apply rate limiting based on authentication
Route::middleware([
    'throttle:inspirecms-api-public',
])->group(function () {
    // Content type endpoints (dynamic based on document type)
    Route::get('{type}', [ContentTypeController::class, 'index'])->name('content.index');
    Route::get('{type}/slug/{slug}', [ContentTypeController::class, 'showBySlug'])->name('content.show.slug');
    Route::get('{type}/{id}', [ContentTypeController::class, 'show'])->name('content.show');
});

// Protected write endpoints
Route::middleware([
    'inspirecms.api.auth',
    'throttle:inspirecms-api-authenticated',
])->group(function () {
    Route::post('{type}', [ContentTypeController::class, 'store'])->name('content.store');
    Route::put('{type}/{id}', [ContentTypeController::class, 'update'])->name('content.update');
    Route::patch('{type}/{id}', [ContentTypeController::class, 'update'])->name('content.patch');
    Route::delete('{type}/{id}', [ContentTypeController::class, 'destroy'])->name('content.destroy');
});
