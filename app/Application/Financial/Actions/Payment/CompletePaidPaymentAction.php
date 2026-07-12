<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Commerce\Actions\Checkout\CompletePaidOrderPaymentAction;
use App\Application\Financial\Actions\Wallet\CompletePaidManualWalletTopUpAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class CompletePaidPaymentAction
{
    public function __construct(
        private readonly CompletePaidOrderPaymentAction      $completePaidOrderPaymentAction,
        private readonly CompletePaidManualWalletTopUpAction $completePaidWalletTopUpAction,
    )
    {
    }

    /**
     * @param Payment $payment
     * @return Model
     * @throws Throwable
     */
    public function execute(Payment $payment): Model
    {
        $payment = Payment::query()
            ->with('payable')
            ->findOrFail($payment->id);

        if (!$payment->isPaid()) {
            throw new DomainException(
                'Only paid payments can be completed.'
            );
        }

        $payable = $payment->payable;

        return match (true) {
            $payable instanceof Order =>
            $this->completePaidOrderPaymentAction->execute($payment),

            $payable instanceof WalletTopUp =>
            $this->completePaidWalletTopUpAction->execute($payment),

            default => throw new DomainException(
                sprintf(
                    'No paid payment completer is registered for payable [%s].',
                    $payment->payable_type,
                )
            ),
        };
    }
}
