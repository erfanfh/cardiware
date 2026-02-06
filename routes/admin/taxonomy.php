<?php

use App\Http\Controllers\Admin\Taxonomy\TaxonomyTermController;
use Illuminate\Support\Facades\Route;

Route::resource('taxonomy-terms', TaxonomyTermController::class)->names('admin.taxonomy_terms')
    ->except(['create', 'show', 'edit']);
Route::post('taxonomy-terms/bulk', [TaxonomyTermController::class, 'bulkAction'])->name('admin.taxonomy_terms.bulk');
