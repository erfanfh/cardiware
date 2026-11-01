<?php

namespace App\Http\Controllers\Admin\Affiliate;

use App\Application\Affiliate\Actions\ResolveAffiliateWithdrawalAction;
use App\Domain\Affiliate\Models\AffiliateWithdrawal;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AffiliateWithdrawalController extends Controller
{
    public function index(): View
    {
        Gate::authorize('affiliate.withdrawals');

        $withdrawals = AffiliateWithdrawal::query()
            ->with('affiliate.user:id,first_name,last_name,email')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.affiliate.withdrawals', compact('withdrawals'));
    }

    public function resolve(Request $request, AffiliateWithdrawal $withdrawal): RedirectResponse
    {
        Gate::authorize('affiliate.withdrawals');

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            app(ResolveAffiliateWithdrawalAction::class)->execute(
                withdrawal: $withdrawal,
                admin: $request->user(),
                approve: $validated['decision'] === 'approve',
                note: $validated['note'] ?? null,
            );
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'وضعیت درخواست برداشت ثبت شد.');
    }
}
