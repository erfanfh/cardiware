<?php

namespace App\Http\Controllers\Admin\Raffle;

use App\Application\Raffle\Actions\Campaign\BuildDrawCampaignOperationalAlertsAction;
use App\Application\Raffle\Actions\Campaign\CreateDrawCampaignAction;
use App\Application\Raffle\Actions\Campaign\DeleteDrawCampaignAction;
use App\Application\Raffle\Actions\Campaign\UpdateDrawCampaignAction;
use App\Application\Raffle\Listings\DrawCampaignListing;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\TicketGrant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Raffle\IndexDrawCampaignRequest;
use App\Http\Requests\Admin\Raffle\SaveDrawCampaignRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DrawCampaignController extends Controller
{
    public function index(IndexDrawCampaignRequest $request, DrawCampaignListing $listing): View
    {
        Gate::authorize('raffle-campaign.list');
        $validated = $request->validated();
        $campaigns = $listing->handle(
            DrawCampaign::query()->with(['coverMediaFile'])->withCount('prizes'),
            $validated,
        )->paginate($listing->perPage())->withQueryString();
        $statuses = collect(DrawCampaignStatusEnum::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
        $hasActiveFilters = $listing->hasActiveFilters($validated);

        return view('admin.raffle.campaigns.index', compact('campaigns', 'statuses', 'hasActiveFilters'));
    }

    public function create(): View
    {
        Gate::authorize('raffle-campaign.create');

        return view('admin.raffle.campaigns.create');
    }

    /** @throws Throwable */
    public function store(SaveDrawCampaignRequest $request, CreateDrawCampaignAction $action): RedirectResponse
    {
        Gate::authorize('raffle-campaign.create');
        $campaign = $action->execute($request->validated(), $request->user());

        return redirect()->route('admin.draw-campaigns.edit', $campaign)->with('success', 'قرعه کشی با موفقیت ایجاد شد.');
    }

    public function show(DrawCampaign $drawCampaign): View|RedirectResponse
    {
        if (Gate::allows('raffle-campaign.edit')) {
            return redirect()->route('admin.draw-campaigns.edit', $drawCampaign);
        }

        Gate::authorize('raffle-campaign.show');
        $drawCampaign->load(['coverMediaFile', 'prizes.mediaFile', 'creator']);
        $recentTickets = DrawTicket::query()->where('campaign_id', $drawCampaign->id)->with('user')->latest('id')->limit(8)->get();
        $recentGrants = TicketGrant::query()->where('campaign_id', $drawCampaign->id)->with(['user', 'issuer'])->latest('id')->limit(6)->get();

        return view('admin.raffle.campaigns.show', compact('drawCampaign', 'recentTickets', 'recentGrants'));
    }

    public function edit(DrawCampaign $drawCampaign, BuildDrawCampaignOperationalAlertsAction $health): View
    {
        Gate::authorize('raffle-campaign.edit');
        $drawCampaign->load(['coverMediaFile', 'prizes.mediaFile']);
        $recentAuditEvents = $drawCampaign->auditEvents()->with('actor')->limit(12)->get();
        $raffleRewardOffers = RewardOffer::query()
            ->where('type', RewardTypeEnum::RAFFLE_TICKET->value)
            ->where('target_type', $drawCampaign->getMorphClass())
            ->where('target_id', $drawCampaign->getKey())
            ->withCount('redemptions')
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->get();
        $operationalAlerts = $health->execute($drawCampaign);

        return view('admin.raffle.campaigns.edit', compact('drawCampaign', 'recentAuditEvents', 'raffleRewardOffers', 'operationalAlerts'));
    }

    /** @throws Throwable */
    public function update(SaveDrawCampaignRequest $request, DrawCampaign $drawCampaign, UpdateDrawCampaignAction $action): RedirectResponse
    {
        Gate::authorize('raffle-campaign.edit');
        $action->execute($drawCampaign, $request->validated(), $request->user());

        return redirect()->route('admin.draw-campaigns.edit', $drawCampaign)->with('success', 'قرعه کشی با موفقیت بروزرسانی شد.');
    }

    /** @throws Throwable */
    public function destroy(DrawCampaign $drawCampaign, DeleteDrawCampaignAction $action): RedirectResponse
    {
        Gate::authorize('raffle-campaign.delete');
        $action->execute($drawCampaign);

        return redirect()->route('admin.draw-campaigns.index')->with('success', 'قرعه کشی پیش نویس حذف شد.');
    }
}
