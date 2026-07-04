<?php

use App\Http\Controllers\Front\Support\StoreTicketController;

Route::middleware(['auth'])
    ->prefix('account')
    ->name('account.')
    ->group(function () {
        Route::post('/tickets', StoreTicketController::class)->name('tickets.store');
    });
