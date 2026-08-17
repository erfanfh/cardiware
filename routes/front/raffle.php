<?php

use App\Http\Controllers\Front\Raffle\AccountDrawCampaignController;
use App\Http\Controllers\Front\Raffle\PublicDrawCampaignController;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::middleware(ForceTrailingSlash::class)
    ->prefix('raffles')
    ->name('raffles.')
    ->controller(PublicDrawCampaignController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{slug}', 'show')->name('show');
    });

Route::middleware('auth')
    ->prefix('account')
    ->name('account.')
    ->group(function () {
        Route::get('raffles', AccountDrawCampaignController::class)->name('raffles');
    });
