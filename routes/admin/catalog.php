<?php

use App\Http\Controllers\Admin\Catalog\ProductAnalyticsController;
use App\Http\Controllers\Admin\Catalog\ProductAttributeController;
use App\Http\Controllers\Admin\Catalog\ProductBulkActionController;
use App\Http\Controllers\Admin\Catalog\ProductController;
use App\Http\Controllers\Admin\Catalog\ProductEditLockController;
use App\Http\Controllers\Admin\Catalog\ProductInformationController;
use App\Http\Controllers\Admin\Catalog\ProductMedalController;
use App\Http\Controllers\Admin\Catalog\ProductSearchController;
use App\Http\Controllers\Admin\Catalog\RevealContentKeyController;

Route::resource('products', ProductController::class)->except(['show']);

Route::prefix('products')->name('products.')->group(function () {
    Route::get('{product}/analytics', ProductAnalyticsController::class)->name('analytics');
    Route::post('{product}/edit-lock/renew', [ProductEditLockController::class, 'renew'])->name('edit-lock.renew');
    Route::post('{product}/edit-lock/release', [ProductEditLockController::class, 'release'])->name('edit-lock.release');
    Route::get('attributes', ProductAttributeController::class)->name('attributes.index');
    Route::get('information', ProductInformationController::class)->name('information.index');
    Route::get('medals', ProductMedalController::class)->name('medals.index');
    Route::post('bulk-action', ProductBulkActionController::class)->name('bulk-action');
    Route::get('ajax-search', ProductSearchController::class)->name('ajax-search');

    // Separate from normal admin auth on purpose -- see RevealContentKeyAction.
    // throttle:10,1 is a coarse route-level backstop; the real per-admin rate
    // limit (5 wrong attempts / 5 minutes) is enforced inside the action itself.
    Route::post('content-key/reveal', RevealContentKeyController::class)
        ->middleware('throttle:10,1')
        ->name('content-key.reveal');
});
