<?php

use App\Http\Controllers\Admin\Content\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('/posts', PostController::class)->except(['show']);
    Route::get('/posts/history', [PostController::class, 'history'])->name('posts.history');
    Route::get('/posts/{id}/restore', [PostController::class, 'restore'])->name('posts.restore');
    Route::delete('/posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('posts.force-delete');
});
