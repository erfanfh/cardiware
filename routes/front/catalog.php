<?php

use App\Http\Controllers\Front\Catalog\ProductDetailController;
use App\Http\Controllers\Front\Catalog\ProductListController;
use App\Http\Middleware\ForceTrailingSlash;

Route::middleware(ForceTrailingSlash::class)
    ->prefix('products')
    ->name('products.')
    ->group(function () {
        Route::get('page/{page}', ProductListController::class)
            ->whereNumber('page')
            ->name('list.page');

        Route::get('/', ProductListController::class)->name('list');

        Route::get('/{productSlug}', ProductDetailController::class)->name('detail');
    });
