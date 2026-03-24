<?php

use App\Http\Controllers\Front\Search\SearchController;

Route::get('search', [SearchController::class, 'index'])->name('search');
