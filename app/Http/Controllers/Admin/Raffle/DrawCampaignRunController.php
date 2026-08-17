<?php

namespace App\Http\Controllers\Admin\Raffle;

use App\Application\Raffle\Actions\Draw\RetryDrawRunAction;
use App\Application\Raffle\Actions\Draw\StartInitialDrawRunAction;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawRun;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Raffle\RetryDrawRunRequest;
use App\Http\Requests\Admin\Raffle\StartDrawRunRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DrawCampaignRunController extends Controller
{
    public function index(DrawCampaign $drawCampaign): View
    {
        Gate::authorize('raffle-draw.list');

        $drawCampaign->loadMissing(['prizes.mediaFile']);

        $runs = $drawCampaign->drawRuns()
            ->with([
                'starter',
                'winners.user',
                'winners.ticket',
                'winners.prize',
            ])
            ->paginate(10)
            ->withQueryString();

        $activeRun = $runs->getCollection()
            ->first(fn(DrawRun $run): bool => in_array($run->status, [
                DrawRunStatusEnum::PENDING,
                DrawRunStatusEnum::RUNNING,
                DrawRunStatusEnum::FAILED,
            ], true));

        $hasInitialRun = $drawCampaign->drawRuns()
            ->where('type', \App\Domain\Raffle\Enums\DrawRunTypeEnum::INITIAL)
            ->exists();

        return view('admin.raffle.draws.index', compact(
            'drawCampaign',
            'runs',
            'activeRun',
            'hasInitialRun',
        ));
    }

    /** @throws Throwable */
    public function store(
        StartDrawRunRequest $request,
        DrawCampaign $drawCampaign,
        StartInitialDrawRunAction $action,
    ): RedirectResponse {
        Gate::authorize('raffle-draw.execute');

        try {
            $run = $action->execute($drawCampaign, $request->user());
        } catch (\DomainException|\LogicException $exception) {
            return back()
                ->with('error', $exception->getMessage())
                ->with('error_title', 'شروع قرعه کشی انجام نشد');
        }

        return redirect()
            ->route('admin.draw-campaigns.draws.index', $drawCampaign)
            ->with('success', 'قرعه کشی Freeze شد و اجرای امن Draw در صف اختصاصی قرار گرفت.')
            ->with('success_title', 'Draw شروع شد');
    }

    /** @throws Throwable */
    public function retry(
        RetryDrawRunRequest $request,
        DrawCampaign $drawCampaign,
        DrawRun $drawRun,
        RetryDrawRunAction $action,
    ): RedirectResponse {
        Gate::authorize('raffle-draw.retry');

        abort_unless((int)$drawRun->campaign_id === (int)$drawCampaign->getKey(), 404);

        try {
            $action->execute($drawRun);
        } catch (\DomainException|\LogicException $exception) {
            return back()
                ->with('error', $exception->getMessage())
                ->with('error_title', 'اجرای مجدد Draw انجام نشد');
        }

        return back()
            ->with('success', 'همان DrawRun با همان Seed و همان Commitment دوباره در صف قرار گرفت.')
            ->with('success_title', 'اجرای مجدد ثبت شد');
    }
}
