<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Commerce\Actions\Checkout\CompletePaidOrderWalletTopUpAction;
use App\Domain\Financial\Enums\WalletTopUpPurposeEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use DomainException;

class CompletePaidWalletTopUpAction
{
    public function __construct(
        private readonly CompletePaidManualWalletTopUpAction $completeManualTopUpAction,
        private readonly CompletePaidOrderWalletTopUpAction  $completeOrderTopUpAction,
    )
    {
    }

    public function execute(Payment $payment): WalletTopUp
    {
        $payment = Payment::query()
            ->with('payable')
            ->findOrFail($payment->id);

        if (!$payment->isPaid()) {
            throw new DomainException(
                'Only paid payments can complete wallet top-up.'
            );
        }

        $topUp = $payment->payable;

        if (!$topUp instanceof WalletTopUp) {
            throw new DomainException(
                'Payment payable is not a wallet top-up.'
            );
        }

        return match ($topUp->purpose) {
            WalletTopUpPurposeEnum::MANUAL => $this->completeManualTopUpAction->execute($payment),
            WalletTopUpPurposeEnum::ORDER_PAYMENT => $this->completeOrderTopUpAction->execute($payment),
        };
    }
}
