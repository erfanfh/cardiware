<?php

use App\Http\Controllers\Admin\Content\PageController;
use App\Http\Controllers\Admin\Content\PostController;
use Illuminate\Support\Facades\Route;

Route::resource('posts', PostController::class)->except(['show']);

Route::prefix('posts')->name('posts.')->controller(PostController::class)->group(function () {
    Route::get('history', 'history')->name('history');
    Route::post('{id}/restore', 'restore')->name('restore');
    Route::delete('{id}/force-delete', 'forceDelete')->name('force-delete');
});

Route::resource('pages', PageController::class)->except(['show']);
