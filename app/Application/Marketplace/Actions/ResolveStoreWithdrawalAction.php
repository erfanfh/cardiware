<?php

namespace App\Application\Marketplace\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreWithdrawalStatusEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreWithdrawal;
use DomainException;
use Illuminate\Support\Facades\DB;

class ResolveStoreWithdrawalAction
{
    public function __construct(private readonly GetStoreWithdrawalBalanceAction $balanceAction) {}

    public function approve(StoreWithdrawal $withdrawal, User $admin, ?string $reference, ?string $note): StoreWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $admin, $reference, $note): StoreWithdrawal {
            $store = Store::withTrashed()->whereKey($withdrawal->store_id)->lockForUpdate()->firstOrFail();
            $current = StoreWithdrawal::query()->whereKey($withdrawal->getKey())->lockForUpdate()->firstOrFail();
            if ($current->status !== StoreWithdrawalStatusEnum::PENDING) {
                throw new DomainException('این درخواست قبلاً رسیدگی شده است.');
            }

            $balance = $this->balanceAction->execute($store);
            if ($balance['balance'] < $current->amount) {
                throw new DomainException('ماندهٔ فروشگاه برای تأیید این درخواست کافی نیست. وضعیت فروش یا درخواست را بررسی کنید.');
            }

            $current->forceFill([
                'status' => StoreWithdrawalStatusEnum::APPROVED,
                'approved_by_user_id' => $admin->getKey(),
                'approved_at' => now(),
                'payment_reference' => $reference,
                'admin_note' => $note,
            ])->save();

            return $current;
        });
    }

    public function reject(StoreWithdrawal $withdrawal, User $admin, string $reason): StoreWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $admin, $reason): StoreWithdrawal {
            Store::withTrashed()->whereKey($withdrawal->store_id)->lockForUpdate()->firstOrFail();
            $current = StoreWithdrawal::query()->whereKey($withdrawal->getKey())->lockForUpdate()->firstOrFail();
            if ($current->status !== StoreWithdrawalStatusEnum::PENDING) {
                throw new DomainException('این درخواست قبلاً رسیدگی شده است.');
            }

            $current->forceFill([
                'status' => StoreWithdrawalStatusEnum::REJECTED,
                'rejected_by_user_id' => $admin->getKey(),
                'rejected_at' => now(),
                'admin_note' => $reason,
            ])->save();

            return $current;
        });
    }
}
