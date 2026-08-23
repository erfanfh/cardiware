<?php

use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\Launcher\LauncherBridgeController;
use App\Http\Controllers\Front\Launcher\LauncherCheckoutEntryController;
use App\Http\Controllers\Front\Launcher\LauncherLandingController;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RecordDailyLoyaltyVisit;
use App\Http\Middleware\RedirectToCanonicalUrl;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', RedirectToCanonicalUrl::class, HandleSeoRedirectsMiddleware::class, RecordDailyLoyaltyVisit::class])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/launcher', LauncherLandingController::class)
            ->name('launcher.landing');
        Route::get('/launcher/open/product/{saleItem}', [LauncherBridgeController::class, 'product'])
            ->whereNumber('saleItem')
            ->name('launcher.open-product');
        Route::get('/launcher/purchase-complete', [LauncherBridgeController::class, 'purchaseComplete'])
            ->middleware(['signed', 'throttle:30,1'])
            ->name('launcher.purchase-complete');
        Route::get(
            '/launcher/checkout/{checkoutSession}/{state}',
            LauncherCheckoutEntryController::class
        )
            ->middleware(['auth', 'signed', 'throttle:30,1'])
            ->name('launcher.checkout.entry');
        require __DIR__ . '/front/identity.php';
        require __DIR__ . '/front/project.php';
        require __DIR__ . '/front/search.php';
        require __DIR__ . '/front/download.php';
        require __DIR__ . '/front/discussion.php';
        require __DIR__ . '/front/taxonomy.php';
        require __DIR__ . '/front/seo.php';
        require __DIR__ . '/front/catalog.php';
        require __DIR__ . '/front/commerce.php';
        require __DIR__ . '/front/marketplace.php';
        require __DIR__ . '/front/financial.php';
        require __DIR__ . '/front/system.php';
        require __DIR__ . '/front/loyalty.php';
        require __DIR__ . '/front/support.php';
        require __DIR__ . '/front/raffle.php';
        require __DIR__ . '/front/poll.php';
        require __DIR__ . '/front/content.php'; // This must be the last
    });
