<?php

use App\Http\Controllers\Admin\Taxonomy\TagController;
use Illuminate\Support\Facades\Route;

Route::resource('tags', TagController::class)->names('admin.tags')
    ->except(['create', 'show', 'edit']);

Route::post('tags/bulk', [TagController::class, 'bulkAction'])->name('admin.tags.bulk');
