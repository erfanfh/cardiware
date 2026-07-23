<?php

namespace App\Application\Financial\Actions\Payment;

use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class FinalizeVerifiedPaymentAction
{
    public function __construct(
        private readonly CompletePaidPaymentAction $completePaidPaymentAction,
        private readonly HandleFailedPaymentAction $handleFailedPaymentAction,
    )
    {
    }

    public function execute(Payment $payment): Model
    {
        $payment = Payment::query()
            ->with('payable')
            ->findOrFail($payment->id);

        if ($payment->isPaid()) {
            return $this->completePaidPaymentAction->execute($payment);
        }

        if (in_array($payment->status, [
            PaymentStatusEnum::FAILED,
            PaymentStatusEnum::CANCELLED,
            PaymentStatusEnum::EXPIRED,
        ], true)) {
            return $this->handleFailedPaymentAction->execute($payment);
        }

        /*
         * INITIATED or PENDING:
         * no terminal side effect should run yet.
         */
        return $payment;
    }
}
