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
            'library/{purchaseAccess}/update/{updateId}/files/{file}/download',
            PrepareLauncherContentDownloadController::class
        )
            ->defaults('contentType', 'update')
            ->whereNumber('purchaseAccess')
            ->whereNumber('updateId')
            ->whereNumber('file')
            ->name('library.updates.files.download');

        Route::post(
            'library/{purchaseAccess}/mods/{modId}/files/{file}/download',
            PrepareLauncherContentDownloadController::class
        )
            ->defaults('contentType', 'mod')
            ->whereNumber('purchaseAccess')
            ->whereNumber('modId')
            ->whereNumber('file')
            ->name('library.mods.files.download');
    });
