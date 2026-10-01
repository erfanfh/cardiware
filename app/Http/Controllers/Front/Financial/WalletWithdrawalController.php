<?php

namespace App\Http\Controllers\Front\Financial;

use App\Application\Financial\Actions\Wallet\CreateWalletWithdrawalAction;
use App\Application\Financial\Exceptions\InsufficientWalletBalanceException;
use App\Application\Financial\Exceptions\InsufficientWithdrawableBalanceException;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Financial\Exceptions\WalletUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Financial\StoreWalletWithdrawalRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;

class WalletWithdrawalController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->to(route('account.wallet') . '#wallet-withdrawal');
    }

    public function store(StoreWalletWithdrawalRequest $request, CreateWalletWithdrawalAction $create): RedirectResponse
    {
        $data = $request->validated();
        $walletUrl = route('account.wallet') . '#wallet-withdrawal';

        try {
            $create->execute(
                $request->user(),
                (int)$data['amount'],
                $data['iban'],
                $data['card_number'],
                $data['account_holder'],
                $data['user_note'] ?? null,
                $data['request_key'],
            );
        } catch (InsufficientWithdrawableBalanceException|InsufficientWalletBalanceException $exception) {
            return redirect()->to($walletUrl)->withErrors(['amount' => 'موجودی قابل برداشت آزاد برای این مبلغ کافی نیست.'], 'walletWithdrawal')->withInput();
        } catch (WalletUnavailableException|DomainException $exception) {
            return redirect()->to($walletUrl)->withErrors(['withdrawal' => $exception->getMessage()], 'walletWithdrawal')->withInput();
        } catch (WalletIntegrityException $exception) {
            report($exception);
            return redirect()->to($walletUrl)->withErrors(['withdrawal' => 'وضعیت موجودی کیف پول نیاز به بررسی دارد. درخواست ثبت نشد.'], 'walletWithdrawal')->withInput();
        }

        return redirect()->to($walletUrl)
            ->with('wallet_withdrawal_success', 'درخواست برداشت وجه ثبت شد و پس از بررسی توسط پشتیبانی، نتیجه به شما اعلام خواهد شد.');
    }
}
