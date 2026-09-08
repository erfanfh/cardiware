<?php

use App\Http\Controllers\Front\Support\ShowTicketController;
use App\Http\Controllers\Front\Support\StoreTicketController;
use App\Http\Controllers\Front\Support\StoreTicketMessageController;
use App\Http\Controllers\Front\Support\TicketAttachmentController;

Route::middleware(['auth', 'throttle:240,1'])
    ->get('/support/ticket-images/{ticketMessageAttachment}/{variant}', TicketAttachmentController::class)
    ->where('variant', 'original|thumbnail|medium|medium_large|large')
    ->name('support.ticket-attachments.show');

Route::middleware(['auth'])
    ->prefix('account')
    ->name('account.')
    ->group(function () {
        Route::post('/tickets', StoreTicketController::class)
            ->middleware('throttle:10,1')
            ->name('tickets.store');

        Route::get('/tickets/{ticket}', ShowTicketController::class)
            ->name('tickets.show');

        Route::post('/tickets/{ticket}/messages', StoreTicketMessageController::class)
            ->middleware('throttle:20,1')
            ->name('tickets.messages.store');
    });
