<?php

use App\Http\Controllers\Admin\Content\PageBulkActionController;
use App\Http\Controllers\Admin\Content\PageController;
use App\Http\Controllers\Admin\Content\PageSearchController;
use App\Http\Controllers\Admin\Content\PostBulkActionController;
use App\Http\Controllers\Admin\Content\PostController;
use App\Http\Controllers\Admin\Content\PostEditLockController;
use App\Http\Controllers\Admin\Content\PostSearchController;
use App\Http\Controllers\Admin\Content\QuickUpdatePageController;
use App\Http\Controllers\Admin\Content\QuickUpdatePostController;
use Illuminate\Support\Facades\Route;

Route::resource('posts', PostController::class)->except(['show']);

Route::prefix('posts')->name('posts.')->controller(PostController::class)->group(function () {
    Route::post('bulk-action', PostBulkActionController::class)->name('bulk-action');
    Route::patch('{post}/quick-update', QuickUpdatePostController::class)->name('quick-update');
    Route::post('{post}/edit-lock/renew', [PostEditLockController::class, 'renew'])->name('edit-lock.renew');
    Route::post('{post}/edit-lock/release', [PostEditLockController::class, 'release'])->name('edit-lock.release');
    Route::get('ajax-search', PostSearchController::class)->name('ajax-search');
});

Route::resource('pages', PageController::class)->except(['show']);
Route::prefix('pages')->name('pages.')->controller(PageController::class)->group(function () {
    Route::post('bulk-action', PageBulkActionController::class)->name('bulk-action');
    Route::patch('{page}/quick-update', QuickUpdatePageController::class)->name('quick-update');
    Route::get('ajax-search', PageSearchController::class)->name('ajax-search');
});
