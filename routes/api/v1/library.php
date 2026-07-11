<?php

use App\Http\Controllers\Api\V1\Library\LibraryController;
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
    });
