<?php

use App\Http\Controllers\Front\Loyalty\AudienceClubLeaderboardController;
use App\Http\Controllers\Front\Loyalty\CompleteTimeSpentController;
use App\Http\Controllers\Front\Loyalty\PendingEarnedNotificationController;
use App\Http\Controllers\Front\Loyalty\StartTimeSpentController;
use Illuminate\Support\Facades\Route;

Route::get('loyalty/leaderboard', AudienceClubLeaderboardController::class)
    ->name('loyalty.leaderboard');

Route::middleware('auth')->prefix('loyalty')->group(function () {
    Route::post('earned-notifications', PendingEarnedNotificationController::class)
        ->middleware('throttle:120,1')
        ->block(5, 5)
        ->name('loyalty.earned-notifications.index');

    Route::post('time-spent/start', StartTimeSpentController::class)
        ->name('loyalty.time-spent.start');

    Route::post('time-spent/complete', CompleteTimeSpentController::class)
        ->name('loyalty.time-spent.complete');
});
