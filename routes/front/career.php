<?php

use App\Http\Controllers\Front\Career\CareerPageController;
use App\Http\Controllers\Front\Career\SubmitCareerApplicationController;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::get('careers', CareerPageController::class)
    ->middleware(ForceTrailingSlash::class)
    ->name('careers.index');

Route::post('careers/apply', SubmitCareerApplicationController::class)
    ->middleware('throttle:5,1')
    ->name('careers.apply');
