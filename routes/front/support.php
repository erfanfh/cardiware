<?php

use App\Http\Controllers\Front\Support\ShowTicketController;
use App\Http\Controllers\Front\Support\StoreTicketController;
use App\Http\Controllers\Front\Support\StoreTicketMessageController;

Route::middleware(['auth'])
    ->prefix('account')
    ->name('account.')
    ->group(function () {
        Route::post('/tickets', StoreTicketController::class)
            ->name('tickets.store');

        Route::get('/tickets/{ticket}', ShowTicketController::class)
            ->name('tickets.show');

        Route::post('/tickets/{ticket}/messages', StoreTicketMessageController::class)
            ->name('tickets.messages.store');
    });
