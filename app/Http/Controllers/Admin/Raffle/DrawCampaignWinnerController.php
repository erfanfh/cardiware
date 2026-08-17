<?php

namespace App\Http\Controllers\Admin\Raffle;

use App\Application\Raffle\Actions\Draw\StartRedrawRunAction;
use App\Application\Raffle\Actions\Winner\ConfirmDrawWinnerAction;
use App\Application\Raffle\Actions\Winner\DeliverDrawPrizeAction;
use App\Application\Raffle\Actions\Winner\PromoteReserveWinnerAction;
use App\Application\Raffle\Actions\Winner\RejectDrawWinnerAction;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawWinner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Raffle\DeliverDrawPrizeRequest;
use App\Http\Requests\Admin\Raffle\RejectDrawWinnerRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DrawCampaignWinnerController extends Controller
{
    public function index(DrawCampaign $drawCampaign): View
    {
        Gate::authorize('raffle-winner.list');

        $drawCampaign->loadMissing(['prizes.mediaFile']);
        $winners = DrawWinner::query()
            ->where('campaign_id', $drawCampaign->getKey())
            ->with(['user', 'ticket', 'prize', 'drawRun', 'confirmer', 'rejector', 'promoter', 'deliverer', 'replacedWinner', 'replacement'])
            ->orderBy('prize_id')
            ->orderByRaw("CASE WHEN kind = 'primary' THEN 0 ELSE 1 END")
            ->orderBy('selected_at')
            ->orderBy('position')
            ->get();

        $byPrize = $winners->groupBy('prize_id');
        $stats = [
            'primary' => $winners->where('kind', DrawWinnerKindEnum::PRIMARY)->count(),
            'confirmed' => $winners->where('status', DrawWinnerStatusEnum::CONFIRMED)->count(),
            'delivered' => $winners->where('status', DrawWinnerStatusEnum::DELIVERED)->count(),
            'pending' => $winners->where('status', DrawWinnerStatusEnum::PENDING)->filter(fn(DrawWinner $winner) => $winner->isEffectivePrimary())->count(),
        ];

        return view('admin.raffle.winners.index', compact('drawCampaign', 'winners', 'byPrize', 'stats'));
    }

    /** @throws Throwable */
    public function confirm(DrawCampaign $drawCampaign, DrawWinner $drawWinner, ConfirmDrawWinnerAction $action): RedirectResponse
    {
        Gate::authorize('raffle-winner.confirm');
        $this->ensureBelongs($drawCampaign, $drawWinner);

        try {
            $action->execute($drawWinner, request()->user());
        } catch (\DomainException|\LogicException $exception) {
            return back()->with('error', $exception->getMessage())->with('error_title', 'تایید برنده انجام نشد');
        }

        return back()->with('success', 'برنده با موفقیت تایید شد.')->with('success_title', 'تایید برنده');
    }

    /** @throws Throwable */
    public function reject(RejectDrawWinnerRequest $request, DrawCampaign $drawCampaign, DrawWinner $drawWinner, RejectDrawWinnerAction $action): RedirectResponse
    {
        Gate::authorize('raffle-winner.reject');
        $this->ensureBelongs($drawCampaign, $drawWinner);

        try {
            $result = $action->execute($drawWinner, $request->validated('reason'), $request->user());
        } catch (\DomainException|\LogicException $exception) {
            return back()->with('error', $exception->getMessage())->with('error_title', 'رد برنده انجام نشد');
        }

        $message = $result['replacement']
            ? 'برنده رد شد و اولین برنده ذخیره به صورت خودکار جایگزین شد.'
            : 'برنده رد شد. برنده ذخیره ای باقی نمانده و این جایگاه به Redraw نیاز دارد.';

        return back()->with('success', $message)->with('success_title', 'وضعیت برنده بروزرسانی شد');
    }

    /** @throws Throwable */
    public function promote(DrawCampaign $drawCampaign, DrawWinner $drawWinner, PromoteReserveWinnerAction $action): RedirectResponse
    {
        Gate::authorize('raffle-winner.promote');
        $this->ensureBelongs($drawCampaign, $drawWinner);

        try {
            $replacement = $action->execute($drawWinner, request()->user());
        } catch (\DomainException|\LogicException $exception) {
            return back()->with('error', $exception->getMessage())->with('error_title', 'جایگزینی ذخیره انجام نشد');
        }

        if (!$replacement) {
            return back()->with('error', 'برنده ذخیره قابل استفاده ای برای این جایزه باقی نمانده است. از Redraw استفاده کنید.')->with('error_title', 'نیاز به Redraw');
        }

        return back()->with('success', 'برنده ذخیره با موفقیت جایگزین شد.')->with('success_title', 'جایگزینی انجام شد');
    }

    /** @throws Throwable */
    public function deliver(DeliverDrawPrizeRequest $request, DrawCampaign $drawCampaign, DrawWinner $drawWinner, DeliverDrawPrizeAction $action): RedirectResponse
    {
        Gate::authorize('raffle-winner.deliver');
        $this->ensureBelongs($drawCampaign, $drawWinner);

        try {
            $action->execute($drawWinner, $request->validated('note'), $request->user());
        } catch (\DomainException|\LogicException $exception) {
            return back()->with('error', $exception->getMessage())->with('error_title', 'تحویل جایزه ثبت نشد');
        }

        return back()->with('success', 'تحویل جایزه با موفقیت ثبت شد.')->with('success_title', 'جایزه تحویل شد');
    }

    /** @throws Throwable */
    public function redraw(DrawCampaign $drawCampaign, DrawWinner $drawWinner, StartRedrawRunAction $action): RedirectResponse
    {
        Gate::authorize('raffle-draw.execute');
        $this->ensureBelongs($drawCampaign, $drawWinner);

        try {
            $action->execute($drawWinner, request()->user());
        } catch (\DomainException|\LogicException $exception) {
            return back()->with('error', $exception->getMessage())->with('error_title', 'Redraw شروع نشد');
        }

        return redirect()->route('admin.draw-campaigns.draws.index', $drawCampaign)
            ->with('success', 'Redraw با Seed مستقل ساخته شد و در صف اجرای قرعه کشی قرار گرفت.')
            ->with('success_title', 'Redraw شروع شد');
    }

    private function ensureBelongs(DrawCampaign $campaign, DrawWinner $winner): void
    {
        abort_unless((int)$winner->campaign_id === (int)$campaign->getKey(), 404);
    }
}
