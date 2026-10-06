<?php

use App\Http\Controllers\Front\Vip\AddVipPlanToCartController;
use App\Http\Controllers\Front\Vip\VipPlanPageController;
use App\Http\Controllers\Front\Vip\VipProductListController;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::get('/vip', VipPlanPageController::class)
    ->middleware(ForceTrailingSlash::class)
    ->name('vip.index');

Route::middleware(ForceTrailingSlash::class)
    ->prefix('vip-products')
    ->name('vip.products.')
    ->group(function () {
        Route::get('page/{page}', VipProductListController::class)
            ->whereNumber('page')
            ->name('page');
        Route::get('/', VipProductListController::class)->name('index');
    });

Route::post('/vip/{vipPlan}/cart', AddVipPlanToCartController::class)
    ->middleware(['auth', 'throttle:20,1'])
    ->name('vip.cart.add');
