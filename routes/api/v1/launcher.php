<?php

use App\Http\Controllers\Api\V1\Launcher\LauncherBootstrapController;
use Illuminate\Support\Facades\Route;

Route::get('launcher/bootstrap', LauncherBootstrapController::class)
    ->middleware('throttle:api')
    ->name('launcher.bootstrap');
