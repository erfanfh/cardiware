<?php

use App\Http\Controllers\Front\Financial\PaymentCallbackController;
use App\Http\Controllers\Front\Financial\PaymentFailedController;
use App\Http\Controllers\Front\Financial\PaymentRetryController;
use App\Http\Controllers\Front\Financial\PaymentSuccessController;

Route::get('/payment/{gateway}/callback', PaymentCallbackController::class)
    ->name('payment.callback');

Route::get('/payment/success/{payment:tracking_code}', PaymentSuccessController::class)
    ->name('payment.success');

Route::get('/payment/failed/{payment:tracking_code}', PaymentFailedController::class)
    ->name('payment.failed');

Route::post('/payment/retry/{payment:tracking_code}', PaymentRetryController::class)
    ->name('payment.retry');
