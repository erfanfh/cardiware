<?php

use App\Http\Controllers\Front\Video\VideoController;
use Illuminate\Support\Facades\Route;

Route::prefix('videos')->name('videos.')->controller(VideoController::class)->group(function (): void {
    Route::get('/', 'index')->name('index');
    Route::get('category/{path}', 'taxonomy')->defaults('taxonomy', 'category')->where('path', '.+')->name('categories');
    Route::get('tag/{path}', 'taxonomy')->defaults('taxonomy', 'tag')->where('path', '.+')->name('tags');
    Route::get('{videoSlug}', 'show')->where('videoSlug', '[^/]+')->name('show');
});
