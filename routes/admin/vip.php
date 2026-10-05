<?php

use App\Http\Controllers\Admin\Vip\VipPlanController;
use Illuminate\Support\Facades\Route;

Route::resource('vip-plans', VipPlanController::class)->except(['show']);
