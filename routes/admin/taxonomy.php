<?php

use App\Http\Controllers\Admin\Taxonomy\TaxonomyTermController;
use Illuminate\Support\Facades\Route;

Route::prefix('taxonomy-terms')->name('admin.taxonomy.terms.')->group(function () {
    Route::get('/', [TaxonomyTermController::class, 'index'])->name('index');
    Route::post('/', [TaxonomyTermController::class, 'store'])->name('store');
    Route::put('/{term}', [TaxonomyTermController::class, 'update'])->name('update');
    Route::delete('/{term}', [TaxonomyTermController::class, 'destroy'])->name('destroy');
    Route::post('/bulk', [TaxonomyTermController::class, 'bulkAction'])->name('bulk');
});
