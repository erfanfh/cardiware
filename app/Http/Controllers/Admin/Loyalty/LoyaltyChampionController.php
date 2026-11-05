<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Domain\Loyalty\Models\LoyaltyChampion;
use App\Domain\Loyalty\Models\LoyaltyPeriodClosure;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;

/**
 * برترین‌های باشگاه در پنل: دوره‌های بسته‌شده، برندگان و جوایزشان، و ثبت تحویل جایزه‌ی ویژه‌ی سال.
 */
final class LoyaltyChampionController extends Controller
{
    public function index(): View
    {
        Gate::authorize('loyalty-rule.list');

        return view('admin.loyalty.champions.index', [
            'closures' => LoyaltyPeriodClosure::query()
                ->with('champions.user')
                ->latest('ends_at')
                ->paginate(20),
            'pendingGrandPrizes' => LoyaltyChampion::query()
                ->where('grand_prize_status', 'pending')
                ->with('user')
                ->get(),
        ]);
    }

    public function deliverGrandPrize(LoyaltyChampion $champion): RedirectResponse
    {
        Gate::authorize('loyalty-rule.list');
        abort_unless($champion->grand_prize_status === 'pending', 404);

        $champion->forceFill(['grand_prize_status' => 'delivered', 'grand_prize_delivered_at' => now()])->save();

        return back()->with('success', 'تحویل جایزه ویژه ثبت شد.');
    }

    /** اجرای دستی بستن دوره‌های تمام‌شده (همان کاری که هر شب خودکار انجام می‌شود). */
    public function closeFinished(): RedirectResponse
    {
        Gate::authorize('loyalty-rule.list');
        Artisan::call('loyalty:close-champion-periods');

        return back()->with('success', 'دوره‌های تمام‌شده بررسی شدند. ' . trim(Artisan::output()));
    }
}
