<?php

use App\Http\Controllers\Api\V1\Download\PrepareDownloadController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'user.active',
    'abilities:downloads:prepare',
])
    ->group(function () {
        Route::post(
            'library/{purchaseAccess}/files/{file}/download',
            PrepareDownloadController::class
        )
            ->whereNumber('purchaseAccess')
            ->whereNumber('file')
            ->name('library.files.download');
    });
