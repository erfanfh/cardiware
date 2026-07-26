<?php

use App\Http\Controllers\Admin\Commerce\OrderController;

Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update']);
Route::put('orders/{order}/description', [OrderController::class, 'updateDescription'])->name('orders.update.description');
