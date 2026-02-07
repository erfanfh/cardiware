<?php

use App\Http\Controllers\Admin\Content\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('/posts', PostController::class)->except(['show']);
});
