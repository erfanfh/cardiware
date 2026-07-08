<?php

use App\Http\Controllers\Front\Financial\PaymentCallbackController;
use App\Http\Controllers\Front\Financial\PaymentResultController;

Route::get('/payment/{gateway}/callback', PaymentCallbackController::class)
    ->middleware('throttle:30,1')
    ->name('payment.callback');

Route::get('/payment/result/{paymentToken}', PaymentResultController::class)
    ->middleware(['throttle:30,1'])
    ->name('payment.result');
