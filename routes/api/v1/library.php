<?php

use App\Http\Controllers\Api\V1\Library\LibraryController;
use App\Http\Controllers\Api\V1\Library\LibraryManifestController;
use App\Http\Controllers\Api\V1\Launcher\LauncherContentController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'user.active',
    'launcher.device',
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

        Route::get('{purchaseAccess}/updates', [LauncherContentController::class, 'updates'])
            ->whereNumber('purchaseAccess')
            ->name('updates');
    });
