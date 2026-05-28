<?php

use App\Http\Controllers\Admin\Taxonomy\PostCategoryController;
use App\Http\Controllers\Admin\Taxonomy\PostCategorySearchController;
use App\Http\Controllers\Admin\Taxonomy\PostQuickCreateCategoryController;
use App\Http\Controllers\Admin\Taxonomy\PostTagController;
use App\Http\Controllers\Admin\Taxonomy\PostTagSearchController;
use App\Http\Controllers\Admin\Taxonomy\ProductCategoryController;
use App\Http\Controllers\Admin\Taxonomy\ProductCategorySearchController;
use App\Http\Controllers\Admin\Taxonomy\ProductQuickCreateCategoryController;
use App\Http\Controllers\Admin\Taxonomy\ProductTagController;
use App\Http\Controllers\Admin\Taxonomy\ProductTagSearchController;
use App\Http\Controllers\Admin\Taxonomy\ProjectTagSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('posts/categories')->name('posts.categories.')->group(function () {
    Route::get('/', PostCategoryController::class)->name('index');
    Route::get('ajax-search', PostCategorySearchController::class)->name('ajax-search');
    Route::post('quick-create', PostQuickCreateCategoryController::class)->name('quick-create');
});

Route::prefix('posts/tags')->name('posts.tags.')->group(function () {
    Route::get('/', PostTagController::class)->name('index');
    Route::get('ajax-search', PostTagSearchController::class)->name('ajax-search');
});

Route::prefix('projects/tags')->name('projects.tags.')->group(function () {
    Route::get('ajax-search', ProjectTagSearchController::class)->name('ajax-search');
});

Route::prefix('products/categories')->name('products.categories.')->group(function () {
    Route::get('/', ProductCategoryController::class)->name('index');
    Route::get('ajax-search', ProductCategorySearchController::class)->name('ajax-search');
    Route::post('quick-create', ProductQuickCreateCategoryController::class)->name('quick-create');
});

Route::prefix('products/tags')->name('products.tags.')->group(function () {
    Route::get('/', ProductTagController::class)->name('index');
    Route::get('ajax-search', ProductTagSearchController::class)->name('ajax-search');
});
