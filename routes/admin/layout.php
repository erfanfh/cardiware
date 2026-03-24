<?php

use App\Http\Controllers\Admin\Layout\PageLayoutController;

Route::resource('page-layouts', PageLayoutController::class)->only(['show', 'update']);
