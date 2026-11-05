<?php

use App\Http\Controllers\Front\Festival\FestivalController;
use Illuminate\Support\Facades\Route;

Route::get('festival', [FestivalController::class, 'index'])->name('festival.index');

// لندینگ‌های ساخته‌شده در لندینگ‌ساز پنل (پیش از content.php تا نامک‌های عمومی را نپوشاند).
Route::get('landing/{landingSlug}', \App\Http\Controllers\Front\Content\LandingPageController::class)
    ->where('landingSlug', '[\pL\pN-]+')
    ->name('landing-pages.show');
