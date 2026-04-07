<?php

use App\Http\Controllers\Front\Taxonomy\CategoryListController;
use App\Http\Controllers\Front\Taxonomy\TagListController;

Route::get('tag/{path}', TagListController::class)->name('tags');
Route::get('category/{path}', CategoryListController::class)->where('path', '.*')->name('categories');
