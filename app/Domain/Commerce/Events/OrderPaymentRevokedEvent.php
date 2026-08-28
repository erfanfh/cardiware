<?php

namespace App\Domain\Commerce\Events;

use Carbon\CarbonImmutable;

final readonly class OrderPaymentRevokedEvent
{
    public function __construct(
        public int $orderId,
        public string $reason,
        public CarbonImmutable $occurredAt,
    ) {
    }
}
