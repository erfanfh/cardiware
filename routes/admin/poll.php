<?php

use App\Http\Controllers\Admin\Poll\PollAnalyticsController;
use App\Http\Controllers\Admin\Poll\PollController;
use App\Http\Controllers\Admin\Poll\PollExportController;
use Illuminate\Support\Facades\Route;

Route::resource('polls', PollController::class);
Route::get('polls/{poll}/analytics', PollAnalyticsController::class)->name('polls.analytics');
Route::get('polls/{poll}/export', PollExportController::class)->name('polls.export');
