<?php

use App\Http\Controllers\Admin\Commerce\AnalyticsController;
use App\Http\Controllers\Admin\Commerce\OrderController;

Route::get('analytics', AnalyticsController::class)->name('analytics.index');

Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update']);
Route::put('orders/{order}/description', [OrderController::class, 'updateDescription'])->name('orders.update.description');
