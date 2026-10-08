<?php

use App\Http\Controllers\Admin\Career\CareerApplicationController;
use App\Http\Controllers\Admin\Career\CareerPositionController;
use Illuminate\Support\Facades\Route;

Route::resource('career-positions', CareerPositionController::class)->except('show');
Route::get('career-applications', [CareerApplicationController::class, 'index'])->name('career-applications.index');
Route::get('career-applications/{careerApplication}', [CareerApplicationController::class, 'show'])->name('career-applications.show');
Route::put('career-applications/{careerApplication}', [CareerApplicationController::class, 'update'])->name('career-applications.update');
Route::get('career-applications/{careerApplication}/resume', [CareerApplicationController::class, 'resume'])->name('career-applications.resume');
