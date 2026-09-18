<?php

use App\Http\Controllers\Admin\Promotion\CouponBulkActionController;
use App\Http\Controllers\Admin\Promotion\CouponController;

Route::resource('coupons', CouponController::class)->except(['show']);

Route::prefix('coupons')->name('coupons.')->group(function () {
    Route::get('{coupon}/redemptions', [CouponController::class, 'redemptions'])->name('redemptions.index');
    Route::post('bulk-action', CouponBulkActionController::class)->name('bulk-action');
});
