<?php

use App\Http\Controllers\Front\Taxonomy\PostCategoryListController;
use App\Http\Controllers\Front\Taxonomy\PostTagListController;
use App\Http\Middleware\ForceTrailingSlash;

Route::middleware(ForceTrailingSlash::class)->group(function () {
    Route::get('tag/{path}/page/{page}', PostTagListController::class)
        ->whereNumber('page')
        ->name('posts.tags.page');

    Route::get('tag/{path}', PostTagListController::class)
        ->name('posts.tags');

    Route::get('category/{path}/page/{page}', PostCategoryListController::class)
        ->where('path', '.*')
        ->whereNumber('page')
        ->name('posts.categories.page');

    Route::get('category/{path}', PostCategoryListController::class)
        ->where('path', '.*')
        ->name('posts.categories');
});
