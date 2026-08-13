<?php

use App\Http\Controllers\Admin\Seo\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('redirects', RedirectController::class)->name('redirects.index');
