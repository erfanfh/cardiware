<?php

use App\Http\Controllers\Admin\Commerce\AnalyticsController;
use App\Http\Controllers\Admin\Commerce\OrderController;
use App\Http\Controllers\Admin\Commerce\PurchaseDownloadResetController;
use App\Http\Controllers\Admin\Commerce\PurchaseDownloadResetUserSearchController;

Route::get('analytics', AnalyticsController::class)->name('analytics.index');

Route::prefix('purchase-download-resets')
    ->name('purchase-download-resets.')
    ->group(function () {
        Route::get('/', [PurchaseDownloadResetController::class, 'index'])->name('index');
        Route::get('/users/search', PurchaseDownloadResetUserSearchController::class)->name('users.search');
        Route::post('/{purchaseAccess}/reset', [PurchaseDownloadResetController::class, 'reset'])->name('reset');
    });

Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update']);
Route::put('orders/{order}/description', [OrderController::class, 'updateDescription'])->name('orders.update.description');
