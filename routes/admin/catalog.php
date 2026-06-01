<?php

use App\Http\Controllers\Admin\Catalog\ProductAttributeController;
use App\Http\Controllers\Admin\Catalog\ProductBulkActionController;
use App\Http\Controllers\Admin\Catalog\ProductController;

Route::resource('products', ProductController::class)->except(['show']);

Route::prefix('posts')->name('products.')->group(function () {
    Route::get('attributes', ProductAttributeController::class)->name('attributes.index');
    Route::post('bulk-action', ProductBulkActionController::class)->name('bulk-action');
});
