<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Commerce\Actions\Checkout\CompletePaidOrderPaymentAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Database\Eloquent\Model;

class CompletePaidPaymentAction
{
    public function __construct(
        private readonly CompletePaidOrderPaymentAction $completePaidOrderPaymentAction,
    )
    {
    }

    /**
     * Complete the domain side effects of a paid payment.
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

            default => throw new DomainException(
                sprintf(
                    'No paid payment completer is registered for payable [%s].',
                    $payment->payable_type,
                )
            ),
        };
    }
}
