<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Application\Financial\Actions\Wallet\ResolveWalletWithdrawalAction;
use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Financial\Enums\WalletWithdrawalStatusEnum;
use App\Domain\Financial\Models\WalletWithdrawal;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class WalletWithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('wallet.withdrawals');
        $data = $request->validate(['status' => ['nullable', Rule::enum(WalletWithdrawalStatusEnum::class)]]);
        $withdrawals = WalletWithdrawal::query()
            ->with('user')
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')->paginate(20)->withQueryString();
        $pendingCount = WalletWithdrawal::query()->where('status', WalletWithdrawalStatusEnum::PENDING)->count();

        return view('admin.financial.withdrawals.index', compact('withdrawals', 'pendingCount'));
    }

    public function show(WalletWithdrawal $withdrawal): View
    {
        Gate::authorize('wallet.withdrawals');
        $withdrawal->load(['user', 'wallet', 'hold', 'approvedBy', 'paidBy', 'rejectedBy']);
        return view('admin.financial.withdrawals.show', compact('withdrawal'));
    }

    public function updateSettings(Request $request, UpdateSettingAction $update): RedirectResponse
    {
        Gate::authorize('wallet.settings');
        $data = $request->validate(['withdrawal_enabled' => ['required', 'boolean']]);
        if (!$update->execute('financial', 'withdrawal_enabled', $request->boolean('withdrawal_enabled'))) {
            return back()->with('error', 'ذخیره تنظیمات برداشت انجام نشد.');
        }
        return back()->with('success', 'وضعیت ثبت درخواست برداشت ذخیره شد.');
    }

    public function approve(WalletWithdrawal $withdrawal, ResolveWalletWithdrawalAction $resolve): RedirectResponse
    {
        Gate::authorize('wallet.withdrawals');
        try {
            $resolve->approve($withdrawal, auth()->user());
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        return back()->with('success', 'درخواست تأیید شد و مبلغ تا زمان ثبت واریز رزرو می‌ماند.');
    }

    public function pay(Request $request, WalletWithdrawal $withdrawal, ResolveWalletWithdrawalAction $resolve): RedirectResponse
    {
        Gate::authorize('wallet.withdrawals');
        $data = $request->validate([
            'payment_reference' => ['required', 'string', 'max:160'],
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);
        try {
            $resolve->pay($withdrawal, $request->user(), trim($data['payment_reference']), trim($data['admin_note']));
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        return back()->with('success', 'واریز ثبت شد و مبلغ رزروشده از کیف پول کسر شد.');
    }

    public function reject(Request $request, WalletWithdrawal $withdrawal, ResolveWalletWithdrawalAction $resolve): RedirectResponse
    {
        Gate::authorize('wallet.withdrawals');
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        try {
            $resolve->reject($withdrawal, $request->user(), trim($data['reason']));
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        return back()->with('success', 'درخواست رد شد و مبلغ رزروشده آزاد شد.');
    }
}
