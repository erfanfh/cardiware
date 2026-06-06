<?php

namespace App\Application\Financial\Events;

use App\Domain\Financial\Models\Payment;

class PaymentFailed
{
    public function __construct(
        public readonly Payment $payment,
    )
    {
    }
}
