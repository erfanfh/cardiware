<?php

use App\Http\Controllers\Front\Download\TrackDownloadEntryClickController;
use Illuminate\Support\Facades\Route;

Route::prefix('dl')->name('downloads.')->group(function () {
    Route::post('e/{publicId}/click', TrackDownloadEntryClickController::class)
        ->middleware('signed:relative')
        ->name('entry.track');
});
