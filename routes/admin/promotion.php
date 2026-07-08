<?php

use App\Http\Controllers\Admin\Promotion\CouponBulkActionController;
use App\Http\Controllers\Admin\Promotion\CouponController;

Route::resource('coupons', CouponController::class)->except(['show']);

Route::prefix('coupons')->name('coupons.')->group(function () {
    Route::post('bulk-action', CouponBulkActionController::class)->name('bulk-action');
});
