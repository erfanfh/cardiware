<?php

use App\Http\Controllers\Admin\Promotion\CouponController;

Route::resource('coupons', CouponController::class)->except(['show']);
