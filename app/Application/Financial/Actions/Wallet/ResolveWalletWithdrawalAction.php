<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Enums\WalletHoldTypeEnum;
use App\Domain\Financial\Enums\WalletWithdrawalStatusEnum;
use App\Domain\Financial\Events\WalletWithdrawalResolvedEvent;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletWithdrawal;
use App\Domain\Identity\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ResolveWalletWithdrawalAction
{
    public function __construct(
        private readonly CaptureWalletHoldAction $captureHold,
        private readonly ReleaseWalletHoldAction $releaseHold,
    ) {}

    public function approve(WalletWithdrawal $withdrawal, User $admin): WalletWithdrawal
    {
        return $this->transition($withdrawal, function (WalletWithdrawal $current) use ($admin): void {
            if ($current->status === WalletWithdrawalStatusEnum::APPROVED) {
                return;
            }
            $this->requireActive($current, WalletWithdrawalStatusEnum::PENDING);
            $current->forceFill([
                'status' => WalletWithdrawalStatusEnum::APPROVED,
                'approved_by_user_id' => $admin->id,
                'approved_at' => now(),
            ])->save();
        });
    }

    public function pay(WalletWithdrawal $withdrawal, User $admin, string $reference, string $note): WalletWithdrawal
    {
        return $this->transition($withdrawal, function (WalletWithdrawal $current) use ($admin, $reference, $note): void {
            if ($current->status === WalletWithdrawalStatusEnum::PAID) {
                return;
            }
            $this->requireActive($current, WalletWithdrawalStatusEnum::APPROVED);
            $this->captureHold->execute($current->hold, $admin);
            $current->forceFill([
                'status' => WalletWithdrawalStatusEnum::PAID,
                'paid_by_user_id' => $admin->id,
                'payment_reference' => $reference,
                'admin_note' => $note,
                'paid_at' => now(),
            ])->save();
        });
    }

    public function reject(WalletWithdrawal $withdrawal, User $admin, string $reason): WalletWithdrawal
    {
        return $this->transition($withdrawal, function (WalletWithdrawal $current) use ($admin, $reason): void {
            if ($current->status === WalletWithdrawalStatusEnum::REJECTED) {
                return;
            }
            if (!in_array($current->status, [WalletWithdrawalStatusEnum::PENDING, WalletWithdrawalStatusEnum::APPROVED], true)) {
                throw new DomainException('این درخواست دیگر قابل رد کردن نیست.');
            }
            $this->requireActiveHold($current);
            $this->releaseHold->execute($current->hold, $admin, $reason);
            $current->forceFill([
                'status' => WalletWithdrawalStatusEnum::REJECTED,
                'rejected_by_user_id' => $admin->id,
                'admin_note' => $reason,
                'rejected_at' => now(),
            ])->save();
        });
    }

    private function transition(WalletWithdrawal $withdrawal, callable $callback): WalletWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $callback): WalletWithdrawal {
            Wallet::query()->whereKey($withdrawal->wallet_id)->lockForUpdate()->firstOrFail();
            $current = WalletWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $current->status;
            $callback($current);
            if ($oldStatus !== $current->status && in_array($current->status, [WalletWithdrawalStatusEnum::PAID, WalletWithdrawalStatusEnum::REJECTED], true)) {
                $withdrawalId = (int) $current->getKey();
                DB::afterCommit(fn () => event(new WalletWithdrawalResolvedEvent($withdrawalId)));
            }
            return $current->refresh();
        }, 3);
    }

    private function requireActive(WalletWithdrawal $withdrawal, WalletWithdrawalStatusEnum $expected): void
    {
        if ($withdrawal->status !== $expected) {
            throw new DomainException('وضعیت این درخواست برای عملیات انتخاب‌شده مناسب نیست.');
        }
        $this->requireActiveHold($withdrawal);
    }

    private function requireActiveHold(WalletWithdrawal $withdrawal): void
    {
        $hold = $withdrawal->hold;
        if (!$hold
            || $hold->status !== WalletHoldStatusEnum::ACTIVE
            || $hold->type !== WalletHoldTypeEnum::WITHDRAWAL_REQUEST
            || $hold->wallet_id !== $withdrawal->wallet_id
            || $hold->amount !== $withdrawal->amount
            || $hold->reference_type !== $withdrawal->getMorphClass()
            || $hold->reference_id !== $withdrawal->id) {
            throw new DomainException('رزرو موجودی این درخواست فعال نیست.');
        }
    }
}
