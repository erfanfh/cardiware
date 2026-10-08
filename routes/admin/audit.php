<?php

use App\Http\Controllers\Admin\Audit\AuditEventController;
use Illuminate\Support\Facades\Route;

Route::get('audit-events', [AuditEventController::class, 'index'])->name('audit-events.index');
Route::get('audit-events/{auditEvent}', [AuditEventController::class, 'show'])->name('audit-events.show');
