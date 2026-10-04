<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Application\Marketplace\Actions\GetStoreWithdrawalBalanceAction;
use App\Application\Marketplace\Actions\ResolveStoreWithdrawalAction;
use App\Domain\Marketplace\Enums\StoreWithdrawalStatusEnum;
use App\Domain\Marketplace\Models\StoreWithdrawal;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Setting\Groups\FinancialSettings;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreWithdrawalController extends Controller
{
    public function index(Request $request, FinancialSettings $settings): View
    {
        Gate::authorize('store.withdrawals');
        $data = $request->validate(['status' => ['nullable', Rule::enum(StoreWithdrawalStatusEnum::class)]]);
        $withdrawals = StoreWithdrawal::query()->with(['store', 'requestedBy'])
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')->paginate(20)->withQueryString();
        $pendingCount = StoreWithdrawal::query()->where('status', StoreWithdrawalStatusEnum::PENDING)->count();
        $minimum = (int) $settings->get('store_withdrawal_minimum_amount', FinancialSettings::STORE_WITHDRAWAL_MINIMUM_AMOUNT);
        $maximum = (int) $settings->get('store_withdrawal_maximum_amount', FinancialSettings::STORE_WITHDRAWAL_MAXIMUM_AMOUNT);

        return view('admin.marketplace.withdrawals.index', compact('withdrawals', 'pendingCount', 'minimum', 'maximum'));
    }

    public function show(StoreWithdrawal $withdrawal, GetStoreWithdrawalBalanceAction $balanceAction): View
    {
        Gate::authorize('store.withdrawals');
        $withdrawal->load(['store', 'requestedBy', 'approvedBy', 'rejectedBy']);
        $balance = $balanceAction->execute($withdrawal->store);

        return view('admin.marketplace.withdrawals.show', compact('withdrawal', 'balance'));
    }

    public function updateSettings(Request $request, FinancialSettings $settings): RedirectResponse
    {
        Gate::authorize('store.withdrawals');
        $data = $request->validate([
            'minimum_amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'maximum_amount' => ['required', 'integer', 'gte:minimum_amount', 'max:999999999999'],
        ]);
        DB::transaction(function () use ($settings, $data): void {
            $settings->set('store_withdrawal_minimum_amount', (int) $data['minimum_amount']);
            $settings->set('store_withdrawal_maximum_amount', (int) $data['maximum_amount']);
        });

        return back()->with('success', 'حداقل و حداکثر درخواست وجه فروشگاه ذخیره شد.');
    }

    public function approve(Request $request, StoreWithdrawal $withdrawal, ResolveStoreWithdrawalAction $resolve): RedirectResponse
    {
        Gate::authorize('store.withdrawals');
        $data = $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:160'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        try {
            $resolve->approve($withdrawal, $request->user(), $data['payment_reference'] ?? null, $data['admin_note'] ?? null);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'درخواست تأیید و مبلغ از ماندهٔ فروشگاه کسر شد.');
    }

    public function reject(Request $request, StoreWithdrawal $withdrawal, ResolveStoreWithdrawalAction $resolve): RedirectResponse
    {
        Gate::authorize('store.withdrawals');
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        try {
            $resolve->reject($withdrawal, $request->user(), trim($data['reason']));
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'درخواست رد شد و مبلغ آن دوباره قابل درخواست است.');
    }
}
