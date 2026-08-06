<?php

use App\Http\Controllers\Admin\System\SystemReportController;
use Illuminate\Support\Facades\Route;

Route::get('reports', SystemReportController::class)->name('reports.index');
