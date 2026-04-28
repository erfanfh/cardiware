<?php

use App\Http\Controllers\Front\Search\SearchController;
use App\Http\Middleware\ForceTrailingSlash;

Route::get('search', [SearchController::class, 'index'])->middleware(ForceTrailingSlash::class)->name('search');
