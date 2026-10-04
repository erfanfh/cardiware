<?php

namespace App\Application\Marketplace\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreWithdrawalStatusEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreWithdrawal;
use App\Infrastructure\Services\Setting\Groups\FinancialSettings;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateStoreWithdrawalAction
{
    public function __construct(
        private readonly GetStoreWithdrawalBalanceAction $balanceAction,
        private readonly FinancialSettings $settings,
    ) {}

    public function execute(Store $store, User $seller, array $data): StoreWithdrawal
    {
        return DB::transaction(function () use ($store, $seller, $data): StoreWithdrawal {
            $store = Store::query()->whereKey($store->getKey())->lockForUpdate()->firstOrFail();
            if (! $store->sellers()->whereKey($seller->getKey())->exists()) {
                throw new DomainException('شما دیگر به این فروشگاه دسترسی ندارید.');
            }

            $existing = StoreWithdrawal::query()->where('request_key', $data['request_key'])->first();
            if ($existing) {
                if ($existing->store_id === $store->getKey()
                    && $existing->requested_by_user_id === $seller->getKey()
                    && $existing->status === StoreWithdrawalStatusEnum::PENDING
                    && $existing->amount === (int) $data['amount']
                    && $existing->iban === $data['iban']
                    && $existing->card_number === $data['card_number']
                    && $existing->account_holder === $data['account_holder']
                    && (string) $existing->seller_note === (string) ($data['seller_note'] ?? null)) {
                    return $existing;
                }
                throw new DomainException('این شناسهٔ درخواست قبلاً استفاده شده است. صفحه را دوباره بارگذاری کنید.');
            }

            $minimum = (int) $this->settings->get('store_withdrawal_minimum_amount', FinancialSettings::STORE_WITHDRAWAL_MINIMUM_AMOUNT);
            $maximum = (int) $this->settings->get('store_withdrawal_maximum_amount', FinancialSettings::STORE_WITHDRAWAL_MAXIMUM_AMOUNT);
            $amount = (int) $data['amount'];
            if ($amount < $minimum || $amount > $maximum) {
                throw new DomainException('مبلغ درخواست باید بین حداقل و حداکثر تعیین‌شده باشد.');
            }
            if ($amount > $this->balanceAction->execute($store)['available']) {
                throw new DomainException('ماندهٔ قابل درخواست فروشگاه برای این مبلغ کافی نیست.');
            }

            return StoreWithdrawal::query()->create([
                'store_id' => $store->getKey(),
                'requested_by_user_id' => $seller->getKey(),
                'request_key' => $data['request_key'],
                'status' => StoreWithdrawalStatusEnum::PENDING,
                'amount' => $amount,
                'iban' => $data['iban'],
                'card_number' => $data['card_number'],
                'account_holder' => $data['account_holder'],
                'seller_note' => $data['seller_note'] ?? null,
            ]);
        });
    }
}
