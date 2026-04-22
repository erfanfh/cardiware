<?php

use App\Http\Controllers\Front\Taxonomy\PostCategoryListController;
use App\Http\Controllers\Front\Taxonomy\PostTagListController;

Route::get('tag/{path}', PostTagListController::class)->name('posts.tags');
Route::get('category/{path}', PostCategoryListController::class)->where('path', '.*')->name('posts.categories');
