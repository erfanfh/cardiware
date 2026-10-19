<?php

use App\Http\Controllers\Admin\Notification\AccountAnnouncementController;
use App\Http\Controllers\Admin\Notification\PopupBulkActionController;
use App\Http\Controllers\Admin\Notification\PopupController;
use App\Http\Controllers\Admin\Notification\PopupTargetSearchController;

Route::get('popups/targets/{type}', PopupTargetSearchController::class)->name('popups.targets');
Route::post('popups/bulk-action', PopupBulkActionController::class)->name('popups.bulk-action');
Route::resource('popups', PopupController::class)->except(['show']);
Route::resource('account-announcements', AccountAnnouncementController::class)->except(['show']);
