<?php

use App\Http\Controllers\Admin\Slug\SlugController;
use Illuminate\Support\Facades\Route;

Route::get('slugs/check', [SlugController::class, 'check'])->name('slugs.check');
