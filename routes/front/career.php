<?php

use App\Http\Controllers\Front\Career\SubmitCareerApplicationController;
use Illuminate\Support\Facades\Route;

Route::post('careers/apply', SubmitCareerApplicationController::class)
    ->middleware('throttle:5,1')
    ->name('careers.apply');
