<?php

use App\Http\Controllers\Admin\Catalog\ProductAttributeController;
use App\Http\Controllers\Admin\Catalog\ProductBulkActionController;
use App\Http\Controllers\Admin\Catalog\ProductController;
use App\Http\Controllers\Admin\Catalog\ProductInformationController;

Route::resource('products', ProductController::class)->except(['show']);

Route::prefix('products')->name('products.')->group(function () {
    Route::get('attributes', ProductAttributeController::class)->name('attributes.index');
    Route::get('information', ProductInformationController::class)->name('information.index');
    Route::post('bulk-action', ProductBulkActionController::class)->name('bulk-action');
});
