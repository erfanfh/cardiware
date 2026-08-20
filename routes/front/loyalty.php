<?php

use App\Http\Controllers\Front\Loyalty\CompleteTimeSpentController;
use App\Http\Controllers\Front\Loyalty\StartTimeSpentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('loyalty')->group(function () {
    Route::post('time-spent/start', StartTimeSpentController::class)
        ->name('loyalty.time-spent.start');

    Route::post('time-spent/complete', CompleteTimeSpentController::class)
        ->name('loyalty.time-spent.complete');
});
