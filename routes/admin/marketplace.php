<?php

use App\Http\Controllers\Admin\Marketplace\StoreAnalyticsController;
use App\Http\Controllers\Admin\Marketplace\StoreBulkActionController;
use App\Http\Controllers\Admin\Marketplace\StoreController;
use App\Http\Controllers\Admin\Marketplace\SellerRewardController;
use App\Http\Controllers\Admin\Marketplace\StoreSearchController;
use App\Http\Controllers\Admin\Marketplace\StoreSellerSearchController;
use App\Http\Controllers\Admin\Marketplace\StoreWithdrawalController;

Route::resource('stores', StoreController::class)->except(['show']);

Route::prefix('stores')->name('stores.')->group(function () {
    Route::get('{store}/analytics', StoreAnalyticsController::class)->name('analytics');
    Route::get('ajax-search', StoreSearchController::class)->name('ajax-search');
    Route::get('seller-search', StoreSellerSearchController::class)->name('seller-search');
    Route::post('bulk-action', StoreBulkActionController::class)->name('bulk-action');
});

Route::prefix('seller-rewards')->name('seller-rewards.')->group(function () {
    Route::get('/', [SellerRewardController::class, 'index'])->name('index');
    Route::put('settings', [SellerRewardController::class, 'updateSettings'])->name('settings');
    Route::post('spotlights', [SellerRewardController::class, 'storeSpotlight'])->name('spotlights.store');
    Route::delete('spotlights/{spotlight}', [SellerRewardController::class, 'destroySpotlight'])->name('spotlights.destroy');
    Route::post('close', [SellerRewardController::class, 'closeFinished'])->name('close');
});

Route::prefix('store-withdrawals')->name('store-withdrawals.')->group(function () {
    Route::get('/', [StoreWithdrawalController::class, 'index'])->name('index');
    Route::put('settings', [StoreWithdrawalController::class, 'updateSettings'])->name('settings');
    Route::get('{withdrawal}', [StoreWithdrawalController::class, 'show'])->name('show');
    Route::post('{withdrawal}/approve', [StoreWithdrawalController::class, 'approve'])->name('approve');
    Route::post('{withdrawal}/reject', [StoreWithdrawalController::class, 'reject'])->name('reject');
});
