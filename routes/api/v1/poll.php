<?php

use App\Http\Controllers\Api\V1\Poll\PollController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.optional.sanctum')->prefix('polls')->name('polls.')->group(function (): void {
    Route::get('{pollSlug}', [PollController::class, 'show'])->middleware('throttle:api')->name('show');
    Route::post('{pollSlug}/vote', [PollController::class, 'vote'])->middleware('throttle:poll-votes')->name('vote');
});
