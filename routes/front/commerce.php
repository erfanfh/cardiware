<?php

use App\Http\Controllers\Front\Commerce\CartController;

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
