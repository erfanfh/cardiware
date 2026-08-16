<?php

namespace App\Domain\Commerce\Events;

use Carbon\CarbonImmutable;

final readonly class OrderPaidEvent
{
    public function __construct(
        public int $orderId,
        public CarbonImmutable $occurredAt,
    ) {
    }
}
