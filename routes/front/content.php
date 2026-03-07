<?php

use App\Http\Controllers\Front\Content\PageController;
use App\Http\Controllers\Front\Content\PostController;

Route::get('{page}', [PageController::class, 'detail'])->name('pages.detail');

Route::controller(PostController::class)->name('posts.')->group(function () {
    Route::get('{post}', 'detail')->name('detail');
    Route::get('posts', 'list')->name('list');
});
