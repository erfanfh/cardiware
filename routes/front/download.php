<?php

use App\Http\Controllers\Front\Download\DownloadEntryRedirectController;

Route::prefix('dl')->name('downloads.')->group(function () {
    Route::get('e/{publicId}', DownloadEntryRedirectController::class)->name('entry.redirect');
});
