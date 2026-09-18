<?php

use App\Http\Controllers\Admin\Media\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('media', [MediaController::class, 'index'])->name('media.index');
