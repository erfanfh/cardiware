<?php

use App\Http\Controllers\Admin\Commerce\OrderController;

Route::resource('orders', OrderController::class)->only(['index', 'show']);
