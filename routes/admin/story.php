<?php

use App\Http\Controllers\Admin\Story\StoryBulkActionController;
use App\Http\Controllers\Admin\Story\StoryController;
use Illuminate\Support\Facades\Route;

Route::post('stories/bulk-action', StoryBulkActionController::class)->name('stories.bulk-action');
Route::resource('stories', StoryController::class)->except(['show']);
