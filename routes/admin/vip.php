<?php

use App\Http\Controllers\Admin\Vip\VipPageSettingsController;
use App\Http\Controllers\Admin\Vip\VipPlanBulkActionController;
use App\Http\Controllers\Admin\Vip\VipPlanController;
use App\Http\Controllers\Admin\Vip\VipPlanPurchaseController;
use Illuminate\Support\Facades\Route;

Route::get('vip-plans/page-settings', [VipPageSettingsController::class, 'edit'])->name('vip-plans.page-settings');
Route::put('vip-plans/page-settings', [VipPageSettingsController::class, 'update'])->name('vip-plans.page-settings.update');
Route::get('vip-plans/{vip_plan}/purchases', VipPlanPurchaseController::class)->name('vip-plans.purchases.index');
Route::resource('vip-plans', VipPlanController::class)->except(['show']);
Route::post('vip-plans/bulk-action', VipPlanBulkActionController::class)->name('vip-plans.bulk-action');
