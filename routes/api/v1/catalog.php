<?php

use App\Http\Controllers\Api\V1\Catalog\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth.optional.sanctum',
    'throttle:catalog',
])
    ->prefix('products')
    ->name('products.')
    ->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('{productId}', [ProductController::class, 'show'])->whereNumber('productId')->name('show');
    });
