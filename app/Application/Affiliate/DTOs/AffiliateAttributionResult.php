<?php

namespace App\Application\Affiliate\DTOs;

use App\Domain\Affiliate\Models\AffiliateOrder;

final readonly class AffiliateAttributionResult
{
    public function __construct(
        public AffiliateOrder $order,
        public int $referrerBonusPoints,
        public int $refereeBonusPoints,
    ) {
    }
}
