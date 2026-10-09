<?php

use App\Http\Controllers\Admin\Video\VideoController;
use App\Http\Controllers\Admin\Video\VideoEditLockController;
use App\Http\Controllers\Admin\Video\VideoBulkActionController;
use Illuminate\Support\Facades\Route;

Route::post('videos/bulk-action', VideoBulkActionController::class)->name('videos.bulk-action');
Route::post('videos/{video}/edit-lock/renew', [VideoEditLockController::class, 'renew'])->name('videos.edit-lock.renew');
Route::post('videos/{video}/edit-lock/release', [VideoEditLockController::class, 'release'])->name('videos.edit-lock.release');
Route::resource('videos', VideoController::class)->except('show');
