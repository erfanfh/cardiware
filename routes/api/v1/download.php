<?php

use App\Http\Controllers\Api\V1\Download\PrepareDownloadController;
use App\Http\Controllers\Api\V1\Launcher\PrepareLauncherContentDownloadController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'user.active',
    'launcher.device',
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

        Route::post(
            'library/{purchaseAccess}/{contentType}/{content}/files/{file}/download',
            PrepareLauncherContentDownloadController::class
        )
            ->whereNumber('purchaseAccess')
            ->whereIn('contentType', ['update', 'mod'])
            ->whereNumber('content')
            ->whereNumber('file')
            ->name('library.content.files.download');
    });
