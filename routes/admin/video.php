<?php

use App\Http\Controllers\Admin\Video\VideoController;
use App\Http\Controllers\Admin\Video\VideoBulkActionController;
use Illuminate\Support\Facades\Route;

Route::post('videos/bulk-action', VideoBulkActionController::class)->name('videos.bulk-action');
Route::resource('videos', VideoController::class)->except('show');
