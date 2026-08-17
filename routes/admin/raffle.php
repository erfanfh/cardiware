<?php

use App\Http\Controllers\Admin\Raffle\DrawCampaignController;
use App\Http\Controllers\Admin\Raffle\DrawCampaignParticipantController;
use App\Http\Controllers\Admin\Raffle\DrawCampaignRunController;
use App\Http\Controllers\Admin\Raffle\DrawCampaignStatusController;
use App\Http\Controllers\Admin\Raffle\DrawCampaignTicketController;
use App\Http\Controllers\Admin\Raffle\DrawCampaignUserSearchController;
use Illuminate\Support\Facades\Route;

Route::get('draw-campaigns-user-search', DrawCampaignUserSearchController::class)->name('draw-campaigns.user-search');
Route::resource('draw-campaigns', DrawCampaignController::class);
Route::post('draw-campaigns/{draw_campaign}/transition', DrawCampaignStatusController::class)->name('draw-campaigns.transition');
Route::get('draw-campaigns/{draw_campaign}/tickets', [DrawCampaignTicketController::class, 'index'])->name('draw-campaigns.tickets.index');
Route::post('draw-campaigns/{draw_campaign}/tickets', [DrawCampaignTicketController::class, 'issue'])->name('draw-campaigns.tickets.issue');
Route::post('draw-campaigns/{draw_campaign}/tickets/{draw_ticket}/invalidate', [DrawCampaignTicketController::class, 'invalidate'])->name('draw-campaigns.tickets.invalidate');
Route::get('draw-campaigns/{draw_campaign}/participants', DrawCampaignParticipantController::class)->name('draw-campaigns.participants.index');

Route::get('draw-campaigns/{draw_campaign}/draws', [DrawCampaignRunController::class, 'index'])->name('draw-campaigns.draws.index');
Route::post('draw-campaigns/{draw_campaign}/draws', [DrawCampaignRunController::class, 'store'])->name('draw-campaigns.draws.store');
Route::post('draw-campaigns/{draw_campaign}/draws/{draw_run}/retry', [DrawCampaignRunController::class, 'retry'])->name('draw-campaigns.draws.retry');
