<?php

use App\Http\Controllers\Front\Catalog\ProductDetailController;
use App\Http\Controllers\Front\Catalog\ProductListController;

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', ProductListController::class)->name('list');
    Route::get('/{productSlug}', ProductDetailController::class)->name('detail');
});
