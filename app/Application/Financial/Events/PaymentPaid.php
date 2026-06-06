<?php

namespace App\Application\Financial\Events;

use App\Domain\Financial\Models\Payment;

class PaymentPaid
{
    public function __construct(
        public readonly Payment $payment,
    )
    {
    }
}
