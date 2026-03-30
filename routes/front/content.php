<?php

use App\Http\Controllers\Front\Content\ContentController;
use App\Http\Controllers\Front\Content\PostListController;

Route::get('posts', PostListController::class)->name('posts.list');

//=== Last route ===//
Route::get('/{slug}', ContentController::class)->where('slug', '[^/]+')->name('contents.detail');
