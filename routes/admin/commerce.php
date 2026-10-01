<?php

use App\Http\Controllers\Admin\Commerce\AbandonedCartController;
use App\Http\Controllers\Admin\Commerce\AnalyticsController;
use App\Http\Controllers\Admin\Commerce\OrderController;
use App\Http\Controllers\Admin\Commerce\PurchaseDownloadResetController;
use App\Http\Controllers\Admin\Commerce\PurchaseDownloadResetUserSearchController;
use App\Http\Controllers\Admin\Financial\WalletWithdrawalController;

Route::get('analytics', AnalyticsController::class)->name('analytics.index');

Route::prefix('abandoned-carts')
    ->name('abandoned-carts.')
    ->group(function () {
        Route::get('/', [AbandonedCartController::class, 'index'])->name('index');
        Route::put('/settings', [AbandonedCartController::class, 'updateSettings'])->name('settings.update');
        Route::post('/cleanup', [AbandonedCartController::class, 'cleanupNow'])->name('cleanup');
    });

Route::prefix('purchase-download-resets')
    ->name('purchase-download-resets.')
    ->group(function () {
        Route::get('/', [PurchaseDownloadResetController::class, 'index'])->name('index');
        Route::get('/users/search', PurchaseDownloadResetUserSearchController::class)->name('users.search');
        Route::post('/{purchaseAccess}/reset', [PurchaseDownloadResetController::class, 'reset'])->name('reset');
    });

Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update']);
Route::prefix('wallet-withdrawals')->name('wallet-withdrawals.')->group(function () {
    Route::get('/', [WalletWithdrawalController::class, 'index'])->name('index');
    Route::put('settings', [WalletWithdrawalController::class, 'updateSettings'])->name('settings');
    Route::get('{withdrawal}', [WalletWithdrawalController::class, 'show'])->name('show');
    Route::post('{withdrawal}/approve', [WalletWithdrawalController::class, 'approve'])->name('approve');
    Route::post('{withdrawal}/pay', [WalletWithdrawalController::class, 'pay'])->name('pay');
    Route::post('{withdrawal}/reject', [WalletWithdrawalController::class, 'reject'])->name('reject');
});
Route::put('orders/{order}/description', [OrderController::class, 'updateDescription'])->name('orders.update.description');
Route::post('orders/{order}/descriptions', [OrderController::class, 'updateDescription'])->name('orders.descriptions.store');
