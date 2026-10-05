<?php

use App\Http\Controllers\Front\Vip\AddVipPlanToCartController;
use App\Http\Controllers\Front\Vip\VipPlanPageController;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::get('/vip', VipPlanPageController::class)
    ->middleware(ForceTrailingSlash::class)
    ->name('vip.index');

Route::post('/vip/{vipPlan}/cart', AddVipPlanToCartController::class)
    ->middleware(['auth', 'throttle:20,1'])
    ->name('vip.cart.add');
