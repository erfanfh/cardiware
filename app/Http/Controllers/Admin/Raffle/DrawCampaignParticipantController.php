<?php

namespace App\Http\Controllers\Admin\Raffle;

use App\Application\Raffle\Listings\DrawParticipantListing;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Raffle\IndexDrawParticipantRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DrawCampaignParticipantController extends Controller
{
    public function __invoke(IndexDrawParticipantRequest $request, DrawCampaign $drawCampaign, DrawParticipantListing $listing): View
    {
        Gate::authorize('raffle-participant.list');
        $filters = $request->validated();
        $query = DrawTicket::query()
            ->where('campaign_id', $drawCampaign->id)
            ->select('user_id')
            ->selectRaw('COUNT(*) as total_tickets_count')
            ->selectRaw('SUM(status = ?) as active_tickets_count', [DrawTicketStatusEnum::ACTIVE->value])
            ->selectRaw('SUM(status = ?) as invalidated_tickets_count', [DrawTicketStatusEnum::INVALIDATED->value])
            ->selectRaw('COUNT(DISTINCT ticket_grant_id) as grants_count')
            ->selectRaw('MAX(issued_at) as latest_ticket_at')
            ->groupBy('user_id')
            ->with('user');
        $participants = $listing->handle($query, $filters)->paginate($listing->perPage())->withQueryString();
        $hasActiveFilters = $listing->hasActiveFilters($filters);
        return view('admin.raffle.participants.index', compact('drawCampaign','participants','hasActiveFilters'));
    }
}
