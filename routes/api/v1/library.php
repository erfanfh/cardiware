<?php

use App\Http\Controllers\Api\V1\Library\LibraryController;
use App\Http\Controllers\Api\V1\Library\LibraryManifestController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'abilities:library:read',
])
    ->prefix('library')
    ->name('library.')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])
            ->name('index');

        Route::get('{purchaseAccess}', [LibraryController::class, 'show'])
            ->whereNumber('purchaseAccess')
            ->name('show');

        Route::get('{purchaseAccess}/manifest', LibraryManifestController::class)
            ->whereNumber('purchaseAccess')
            ->name('manifest');
    });
