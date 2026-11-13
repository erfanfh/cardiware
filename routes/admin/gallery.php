<?php

use App\Http\Controllers\Admin\Gallery\GallerySubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('gallery', [GallerySubmissionController::class, 'index'])->name('gallery.index');
Route::put('gallery/settings', [GallerySubmissionController::class, 'storeSettings'])->name('gallery.settings.store');
Route::put('gallery/{submission}', [GallerySubmissionController::class, 'update'])->name('gallery.update');
Route::patch('gallery/{submission}/status', [GallerySubmissionController::class, 'updateStatus'])->name('gallery.status');
Route::delete('gallery/{submission}', [GallerySubmissionController::class, 'destroy'])->name('gallery.destroy');
