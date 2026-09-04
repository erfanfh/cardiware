<?php

namespace App\Application\Commerce\Support;

final readonly class TaxCalculation
{
    public function __construct(
        public int $taxableAmount,
        public int $rateBasisPoints,
        public int $taxAmount,
        public int $totalAmount,
    ) {
    }
}
