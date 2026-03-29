<?php

use App\Http\Controllers\Admin\Taxonomy\CategoryController;
use App\Http\Controllers\Admin\Taxonomy\CategorySearchController;
use App\Http\Controllers\Admin\Taxonomy\QuickCreateCategoryController;
use App\Http\Controllers\Admin\Taxonomy\TagController;
use App\Http\Controllers\Admin\Taxonomy\TagSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', CategoryController::class)->name('index');
    Route::get('ajax-search', CategorySearchController::class)->name('ajax-search');
    Route::post('quick-create', QuickCreateCategoryController::class)->name('quick-create');
});

Route::get('tags', TagController::class)->name('tags.index');
Route::get('tags/ajax-search', TagSearchController::class)->name('tags.ajax-search');
