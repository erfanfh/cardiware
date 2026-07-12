<?php

use App\Http\Controllers\Admin\Support\TicketBulkActionController;
use App\Http\Controllers\Admin\Support\TicketCloseController;
use App\Http\Controllers\Admin\Support\TicketDepartmentController;
use App\Http\Controllers\Admin\Support\TicketDestroyController;
use App\Http\Controllers\Admin\Support\TicketListController;
use App\Http\Controllers\Admin\Support\TicketReopenController;
use App\Http\Controllers\Admin\Support\TicketReplyController;
use App\Http\Controllers\Admin\Support\TicketShowController;
use App\Http\Controllers\Admin\Support\UpdateTicketDepartmentController;

Route::get('ticket-departments', TicketDepartmentController::class)->name('ticket-departments.index');

Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', TicketListController::class)->name('index');
    Route::delete('/{ticket}', TicketDestroyController::class)->name('destroy');
    Route::post('/bulk-action', TicketBulkActionController::class)->name('bulk-action');
    Route::get('/{ticket}', TicketShowController::class)->name('show');
    Route::post('/{ticket}/reply', TicketReplyController::class)->name('reply');
    Route::post('/{ticket}/close', TicketCloseController::class)->name('close');
    Route::post('/{ticket}/reopen', TicketReopenController::class)->name('reopen');
    Route::patch('/{ticket}/department', UpdateTicketDepartmentController::class)->name('department.update');
});
