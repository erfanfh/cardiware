<?php

use App\Http\Controllers\Admin\Taxonomy\CategoryController;
use App\Http\Controllers\Admin\Taxonomy\CategorySearchController;
use App\Http\Controllers\Admin\Taxonomy\TagController;
use Illuminate\Support\Facades\Route;

Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
Route::post('categories/bulk', [CategoryController::class, 'bulkAction'])->name('categories.bulk');
Route::get('categories/fetch', [CategoryController::class, 'categoriesFetch'])->name('categories.fetch');
Route::get('categories/ajax-search', CategorySearchController::class)->name('categories.ajax-search');

Route::resource('tags', TagController::class)->except(['create', 'show', 'edit']);
Route::post('tags/bulk', [TagController::class, 'bulkAction'])->name('tags.bulk');
Route::get('tags/fetch', [TagController::class, 'tagsFetch'])->name('tags.fetch');
