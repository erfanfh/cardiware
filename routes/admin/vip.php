<?php

use App\Http\Controllers\Admin\Vip\VipPlanBulkActionController;
use App\Http\Controllers\Admin\Vip\VipPlanController;
use Illuminate\Support\Facades\Route;

Route::resource('vip-plans', VipPlanController::class)->except(['show']);
Route::post('vip-plans/bulk-action', VipPlanBulkActionController::class)->name('vip-plans.bulk-action');
