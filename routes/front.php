<?php

use App\Http\Controllers\Admin\Content\PostController;
use App\Http\Controllers\Front\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::view('/about-us', 'templates.about-us');
        Route::controller(PostController::class)->name('posts.')->group(function () {
            Route::get('{post}', 'detail')->name('detail');
            Route::get('posts', 'list')->name('list');
        });
        require __DIR__ . '/front/identity.php';
        require __DIR__ . '/front/project.php';
        require __DIR__ . '/front/search.php';
        require __DIR__ . '/front/content.php'; // This must be the last
    });
