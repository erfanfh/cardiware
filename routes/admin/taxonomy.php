<?php

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Admin\Taxonomy\TagController;
use App\Http\Controllers\Admin\Taxonomy\CategoryController;
use Illuminate\Support\Facades\Route;

Route::resource('category', CategoryController::class)->names('admin.taxonomies.category')
    ->except(['show', 'create', 'edit']);
Route::post('category/bulk', [CategoryController::class, 'bulkAction'])->name('admin.taxonomies.category.bulk');

Route::resource('tags', TagController::class)->names('admin.taxonomies.tags')
    ->except(['create', 'show', 'edit']);
Route::post('tags/bulk', [TagController::class, 'bulkAction'])->name('admin.taxonomies.tags.bulk');

Route::get('/admin/categories/fetch', [CategoryController::class, 'categoriesFetch'])->name('admin.taxonomies.categories.fetch');
Route::get('/admin/tags/search', [TagController::class, 'tagsFetch'])->name('admin.taxonomies.tags.search');
