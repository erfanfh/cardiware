<?php

namespace App\Application\Affiliate\Actions;

use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatusEnum;
use App\Domain\Affiliate\Models\AffiliateWithdrawal;
use App\Domain\Identity\Models\User;
use DomainException;

class ResolveAffiliateWithdrawalAction
{
    public function __construct(
        private readonly GetAffiliateOverviewAction $overviewAction,
    ) {
    }

    public function execute(AffiliateWithdrawal $withdrawal, User $admin, bool $approve, ?string $note): AffiliateWithdrawal
    {
        if ($withdrawal->status !== AffiliateWithdrawalStatusEnum::PENDING) {
            throw new DomainException('این درخواست قبلا بررسی شده است.');
        }

        if ($approve) {
            $overview = $this->overviewAction->execute($withdrawal->affiliate);

            // موجودی محاسبه‌شده خودِ این درخواستِ در انتظار را کم کرده؛
            // هنگام تأیید باید مبلغ همان درخواست را برگردانیم.
            $availableForThis = $overview['availableBalance'] + $withdrawal->amount;

            if ($withdrawal->amount > $availableForThis) {
                throw new DomainException('موجودی قابل برداشت همکار برای تأیید این درخواست کافی نیست.');
            }
        }

        $withdrawal->update([
            'status' => $approve
                ? AffiliateWithdrawalStatusEnum::APPROVED->value
                : AffiliateWithdrawalStatusEnum::REJECTED->value,
            'admin_note' => $note,
            'resolved_at' => now(),
        ]);

        return $withdrawal;
    }
}
