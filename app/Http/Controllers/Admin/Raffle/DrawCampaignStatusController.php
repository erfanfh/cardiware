<?php

namespace App\Http\Controllers\Admin\Raffle;

use App\Application\Raffle\Actions\Campaign\TransitionDrawCampaignStatusAction;
use App\Application\Raffle\DTOs\TransitionDrawCampaignStatusDTO;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Raffle\TransitionDrawCampaignRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DrawCampaignStatusController extends Controller
{
    /** @throws Throwable */
    public function __invoke(TransitionDrawCampaignRequest $request, DrawCampaign $drawCampaign, TransitionDrawCampaignStatusAction $action): RedirectResponse
    {
        Gate::authorize('raffle-campaign.transition');
        $data = $request->validated();
        try {
            $campaign = $action->execute(new TransitionDrawCampaignStatusDTO(
                campaign: $drawCampaign,
                status: DrawCampaignStatusEnum::from($data['status']),
                actor: $request->user(),
                reason: $data['reason'] ?? null,
            ));
        } catch (\DomainException|\LogicException $exception) {
            return back()->with('error', $exception->getMessage())->with('error_title', 'تغییر وضعیت انجام نشد');
        }
        return back()->with('success', 'وضعیت قرعه کشی به «'.$campaign->status->label().'» تغییر کرد.');
    }
}
