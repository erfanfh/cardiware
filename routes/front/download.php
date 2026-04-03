<?php

use App\Http\Controllers\Front\Download\CopySectionLinksController;
use App\Http\Controllers\Front\Download\DownloadEntryRedirectController;

Route::prefix('download')->name('downloads.')->group(function () {
    Route::get('e/{publicId}', DownloadEntryRedirectController::class)->name('entry.redirect');
    Route::post('section/{section}/copy-links', CopySectionLinksController::class)->name('section.copy-links');
});
