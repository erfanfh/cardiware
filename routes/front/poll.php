<?php

use App\Http\Controllers\Front\Poll\PollController;
use App\Http\Controllers\Front\Poll\PollIndexController;
use App\Http\Controllers\Front\Poll\PollVoteController;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::prefix('polls')->name('polls.')->group(function (): void {
    Route::get('/', PollIndexController::class)->middleware(ForceTrailingSlash::class)->name('index');
    Route::get('{pollSlug}', PollController::class)->middleware(ForceTrailingSlash::class)->name('show');
    Route::post('{pollSlug}/vote', PollVoteController::class)->middleware('throttle:poll-votes')->name('vote');
});
