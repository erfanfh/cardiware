<?php

use App\Http\Controllers\Front\Raffle\AccountDrawCampaignController;
use App\Http\Controllers\Front\Raffle\AccountDrawWinnerController;
use App\Http\Controllers\Front\Raffle\PublicDrawCampaignController;
use App\Http\Controllers\Front\Raffle\PublicDrawVerificationController;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::middleware(ForceTrailingSlash::class)
    ->prefix('raffles')
    ->name('raffles.')
    ->controller(PublicDrawCampaignController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{raffleSlug}', 'show')->name('show');
        Route::get('{raffleSlug}/verification/{drawRun}', PublicDrawVerificationController::class)
            ->name('verification');
    });

Route::middleware('auth')
    ->prefix('account')
    ->name('account.')
    ->group(function () {
        Route::get('raffles', AccountDrawCampaignController::class)->name('raffles');
        Route::post('raffles/winners/{drawWinner}/claim', [AccountDrawWinnerController::class, 'claim'])
            ->middleware('throttle:raffle-claims')
            ->name('raffles.winners.claim');
        Route::post('raffles/winners/{drawWinner}/decline', [AccountDrawWinnerController::class, 'decline'])
            ->middleware('throttle:raffle-claims')
            ->name('raffles.winners.decline');
    });
