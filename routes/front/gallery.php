<?php

use App\Http\Controllers\Front\Gallery\GalleryController;
use Illuminate\Support\Facades\Route;

Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');

Route::middleware(['auth', 'throttle:5,1'])
    ->post('gallery', [GalleryController::class, 'store'])
    ->name('gallery.store');
