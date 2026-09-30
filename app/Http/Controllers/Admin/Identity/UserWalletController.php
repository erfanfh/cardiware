<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Application\Financial\Actions\Wallet\CreditWalletAction;
use App\Application\Financial\Actions\Wallet\DebitWalletAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\DTOs\CreditWalletDTO;
use App\Application\Financial\DTOs\DebitWalletDTO;
use App\Application\Financial\Exceptions\InsufficientWalletBalanceException;
use App\Application\Financial\Exceptions\WalletIdempotencyConflictException;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Domain\Financial\Enums\WalletDebitPolicyEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Identity\AdjustUserWalletRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UserWalletController extends Controller
{
    public function show(User $user): View
    {
        Gate::authorize('wallet.adjust');

        $wallet = $user->wallet;
        $transactions = $wallet?->transactions()
            ->with('actor')
            ->latest('id')
            ->limit(10)
            ->get() ?? collect();

        return view('admin.identity.users.wallet', compact('user', 'wallet', 'transactions'));
    }

    public function adjust(
        AdjustUserWalletRequest $request,
        User $user,
        GetOrCreateWalletAction $getOrCreateWallet,
        CreditWalletAction $creditWallet,
        DebitWalletAction $debitWallet,
    ): RedirectResponse {
        Gate::authorize('wallet.adjust');

        $data = $request->validated();
        $wallet = $getOrCreateWallet->execute($user);
        $amount = (int) $data['amount'];
        $idempotencyKey = 'admin-wallet-adjust:' . $data['idempotency_key'];

        try {
            if ($data['direction'] === 'credit') {
                $transaction = $creditWallet->execute(new CreditWalletDTO(
                    wallet: $wallet,
                    type: WalletTransactionTypeEnum::ADMIN_CREDIT,
                    amount: $amount,
                    idempotencyKey: $idempotencyKey,
                    withdrawableAmount: $data['withdrawable'] ? $amount : 0,
                    actor: $request->user(),
                    description: $data['note'],
                    metadata: ['source' => 'admin'],
                ));
            } else {
                $transaction = $debitWallet->execute(new DebitWalletDTO(
                    wallet: $wallet,
                    type: WalletTransactionTypeEnum::ADMIN_DEBIT,
                    amount: $amount,
                    idempotencyKey: $idempotencyKey,
                    policy: WalletDebitPolicyEnum::NON_WITHDRAWABLE_FIRST,
                    actor: $request->user(),
                    description: $data['note'],
                    metadata: ['source' => 'admin'],
                ));
            }
        } catch (InsufficientWalletBalanceException $exception) {
            return back()->withErrors(['amount' => 'موجودی آزاد کیف پول برای این کاهش کافی نیست.'])->withInput();
        } catch (WalletIntegrityException $exception) {
            return back()->withErrors(['amount' => 'وضعیت موجودی کیف پول ناسازگار است؛ تغییر ثبت نشد.'])->withInput();
        } catch (WalletIdempotencyConflictException $exception) {
            return back()->withErrors(['idempotency_key' => 'این شناسه عملیات قبلاً برای تغییر دیگری استفاده شده است. صفحه را تازه‌سازی کنید.'])->withInput();
        }

        return redirect()
            ->route('admin.users.wallet.show', $user)
            ->with('success', sprintf(
                '%s موجودی کیف پول ثبت شد. موجودی پس از این تراکنش: %s تومان.',
                $data['direction'] === 'credit' ? 'افزایش' : 'کاهش',
                number_format($transaction->balance_after),
            ))
            ->with('success_title', 'تغییر موجودی ثبت شد');
    }
}
