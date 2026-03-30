<?php

use App\Http\Controllers\Front\Content\ContentController;

//=== Last route ===//
Route::get('/{slug}', ContentController::class)->where('slug', '[^/]+')->name('contents.detail');
