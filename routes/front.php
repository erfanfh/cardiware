<?php

use App\Http\Controllers\Front\HomeController;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RedirectToCanonicalUrl;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', RedirectToCanonicalUrl::class, HandleSeoRedirectsMiddleware::class])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
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
        require __DIR__ . '/front/content.php'; // This must be the last
    });
