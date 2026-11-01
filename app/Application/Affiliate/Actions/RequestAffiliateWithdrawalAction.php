<?php

namespace App\Application\Affiliate\Actions;

use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatusEnum;
use App\Domain\Affiliate\Models\Affiliate;
use App\Domain\Affiliate\Models\AffiliateWithdrawal;
use DomainException;

class RequestAffiliateWithdrawalAction
{
    public function __construct(
        private readonly GetAffiliateOverviewAction $overviewAction,
    ) {
    }

    public function execute(Affiliate $affiliate, int $amount, ?string $destination, ?string $note): AffiliateWithdrawal
    {
        $amount = (int) $amount;

        if ($amount <= 0) {
            throw new DomainException('مبلغ برداشت باید بزرگ‌تر از صفر باشد.');
        }

        if ($amount < $affiliate->min_withdrawal_amount) {
            throw new DomainException(
                'حداقل مبلغ برداشت ' . number_format($affiliate->min_withdrawal_amount) . ' تومان است.',
            );
        }

        $overview = $this->overviewAction->execute($affiliate);

        if ($amount > $overview['availableBalance']) {
            throw new DomainException('مبلغ درخواست از موجودی قابل برداشت شما بیشتر است.');
        }

        if ($overview['pendingWithdrawalAmount'] > 0) {
            throw new DomainException('شما یک درخواست برداشت در انتظار بررسی دارید.');
        }

        return AffiliateWithdrawal::query()->create([
            'affiliate_id' => $affiliate->getKey(),
            'amount' => $amount,
            'status' => AffiliateWithdrawalStatusEnum::PENDING->value,
            'destination' => $destination,
            'user_note' => $note,
            'requested_at' => now(),
        ]);
    }
}
