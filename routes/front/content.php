<?php

use App\Http\Controllers\Front\Content\AuthorController;
use App\Http\Controllers\Front\Content\ContentController;
use App\Http\Controllers\Front\Content\PostListController;

Route::get('posts', PostListController::class)->name('posts.list');
Route::get('author/{user:username}', AuthorController::class)->name('author');

//=== Last route ===//
Route::get('/{slug}', ContentController::class)->where('slug', '[^/]+')->name('contents.detail');
