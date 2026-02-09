<?php

use App\Http\Controllers\Admin\Taxonomy\CategoryController;
use Illuminate\Support\Facades\Route;

Route::resource('category', CategoryController::class)->names('admin.taxonomies.category');
Route::post('category/bulk', [CategoryController::class, 'bulkAction'])->name('admin.taxonomies.category.bulk');
