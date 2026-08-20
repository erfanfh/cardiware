<?php

use App\Http\Controllers\Front\Loyalty\AudienceClubLeaderboardController;
use App\Http\Controllers\Front\Loyalty\CompleteTimeSpentController;
use App\Http\Controllers\Front\Loyalty\StartTimeSpentController;
use Illuminate\Support\Facades\Route;

Route::get('loyalty/leaderboard', AudienceClubLeaderboardController::class)
    ->name('loyalty.leaderboard');

Route::middleware('auth')->prefix('loyalty')->group(function () {
    Route::post('time-spent/start', StartTimeSpentController::class)
        ->name('loyalty.time-spent.start');

    Route::post('time-spent/complete', CompleteTimeSpentController::class)
        ->name('loyalty.time-spent.complete');
});
