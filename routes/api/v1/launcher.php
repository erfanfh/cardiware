<?php

use App\Http\Controllers\Api\V1\Launcher\LauncherBootstrapController;
use App\Http\Controllers\Api\V1\Launcher\LauncherCatalogController;
use App\Http\Controllers\Api\V1\Launcher\LauncherDeviceController;
use App\Http\Controllers\Api\V1\Launcher\LauncherCommerceController;
use App\Http\Controllers\Api\V1\Launcher\LauncherContentController;
use Illuminate\Support\Facades\Route;

Route::get('launcher/bootstrap', LauncherBootstrapController::class)
    ->middleware('throttle:api')
    ->name('launcher.bootstrap');

Route::middleware([
    'auth:sanctum',
    'user.active',
    'launcher.device',
])
    ->group(function (): void {
        Route::get('devices', [LauncherDeviceController::class, 'index'])
            ->name('launcher.devices.index');
        Route::delete('devices/{deviceId}', [LauncherDeviceController::class, 'destroy'])
            ->whereUuid('deviceId')
            ->name('launcher.devices.destroy');

        Route::prefix('launcher')->name('launcher.')->group(function (): void {
            Route::get('catalog', [LauncherCatalogController::class, 'index'])
                ->name('catalog.index');
            Route::get('catalog/{saleItem}', [LauncherCatalogController::class, 'show'])
                ->whereNumber('saleItem')
                ->name('catalog.show');

            Route::post('sale-items/{saleItem}/claim', [LauncherCommerceController::class, 'claim'])
                ->whereNumber('saleItem')
                ->name('sale-items.claim');
            Route::post('sale-items/{saleItem}/checkout', [LauncherCommerceController::class, 'checkout'])
                ->whereNumber('saleItem')
                ->name('sale-items.checkout');
            Route::post('checkout/consume', [LauncherCommerceController::class, 'consume'])
                ->name('checkout.consume');
        });

        Route::get('library/{purchaseAccess}/updates', [LauncherContentController::class, 'updates'])
            ->whereNumber('purchaseAccess')
            ->name('launcher.library.updates');
        Route::get('library/{purchaseAccess}/mods', [LauncherContentController::class, 'mods'])
            ->whereNumber('purchaseAccess')
            ->name('launcher.library.mods');
    });
