<?php

use App\Http\Controllers\Front\Content\AuthorController;
use App\Http\Controllers\Front\Content\ContentController;
use App\Http\Controllers\Front\Content\PostListController;
use App\Http\Middleware\ForceTrailingSlash;

Route::middleware(ForceTrailingSlash::class)->group(function () {
    Route::get('posts', PostListController::class)->name('posts.list');
    Route::get('author/{user:username}/page/{page}', AuthorController::class)
        ->whereNumber('page')
        ->name('author.page');

    Route::get('author/{user:username}', AuthorController::class)
        ->name('author');

    //=== Last route ===//
    Route::get('/{slug}', ContentController::class)->where('slug', '[^/]+')->name('contents.detail');
});
