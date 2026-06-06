<?php

use App\Http\Controllers\Front\Financial\PaymentCallbackController;

Route::get('/payment/{gateway}/callback', PaymentCallbackController::class)
    ->name('payment.callback');
