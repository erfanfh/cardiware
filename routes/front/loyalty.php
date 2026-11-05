<?php

use App\Http\Controllers\Front\Loyalty\AudienceClubLeaderboardController;
use App\Http\Controllers\Front\Loyalty\CompleteTimeSpentController;
use App\Http\Controllers\Front\Loyalty\PendingEarnedNotificationController;
use App\Http\Controllers\Front\Loyalty\StartTimeSpentController;
use Illuminate\Support\Facades\Route;

Route::get('loyalty/leaderboard', AudienceClubLeaderboardController::class)
    ->name('loyalty.leaderboard');

// تالار افتخار: برترین‌های ماه/فصل/سال، رقابت کاربر برتر سال و کانون نخبگان.
Route::get('loyalty/hall-of-fame', App\Http\Controllers\Front\Loyalty\HallOfFameController::class)
    ->middleware('throttle:60,1')
    ->name('loyalty.hall-of-fame');

// کارت عمومی باشگاه هر عضو (جام‌ها، سطح، نشان‌ها).
Route::get('loyalty/members/{user}', App\Http\Controllers\Front\Loyalty\MemberProfileController::class)
    ->whereNumber('user')
    ->middleware('throttle:60,1')
    ->name('loyalty.members.show');

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
