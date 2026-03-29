<?php

use App\Http\Controllers\Admin\Taxonomy\CategoryController;
use App\Http\Controllers\Admin\Taxonomy\CategorySearchController;
use App\Http\Controllers\Admin\Taxonomy\TagController;
use App\Http\Controllers\Admin\Taxonomy\TagSearchController;
use Illuminate\Support\Facades\Route;

Route::get('categories', CategoryController::class)->name('categories.index');
Route::get('categories/ajax-search', CategorySearchController::class)->name('categories.ajax-search');

Route::get('tags', TagController::class)->name('tags.index');
Route::get('tags/ajax-search', TagSearchController::class)->name('tags.ajax-search');
