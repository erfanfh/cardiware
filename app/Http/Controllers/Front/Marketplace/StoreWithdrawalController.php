<?php

namespace App\Http\Controllers\Front\Marketplace;

use App\Application\Marketplace\Actions\CreateStoreWithdrawalAction;
use App\Application\Marketplace\Actions\GetStoreWithdrawalBalanceAction;
use App\Domain\Marketplace\Models\Store;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Marketplace\StoreWithdrawalRequest;
use App\Infrastructure\Services\Setting\Groups\FinancialSettings;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class StoreWithdrawalController extends Controller
{
    public function index(Store $store, GetStoreWithdrawalBalanceAction $balanceAction, FinancialSettings $settings): View
    {
        $balance = $balanceAction->execute($store);
        $withdrawals = $store->withdrawals()->with('requestedBy')->latest('id')->paginate(12);
        $minimum = (int) $settings->get('store_withdrawal_minimum_amount', FinancialSettings::STORE_WITHDRAWAL_MINIMUM_AMOUNT);
        $maximum = (int) $settings->get('store_withdrawal_maximum_amount', FinancialSettings::STORE_WITHDRAWAL_MAXIMUM_AMOUNT);
        $requestKey = old('request_key', (string) Str::uuid());

        return view('front.marketplace.seller.withdrawals', compact('store', 'balance', 'withdrawals', 'minimum', 'maximum', 'requestKey'));
    }

    public function store(StoreWithdrawalRequest $request, Store $store, CreateStoreWithdrawalAction $create): RedirectResponse
    {
        try {
            $create->execute($store, $request->user(), $request->validated());
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['amount' => $exception->getMessage()]);
        }

        return redirect()->route('seller.stores.withdrawals', $store)->with('success', 'درخواست وجه ثبت شد و در انتظار بررسی است.');
    }
}
