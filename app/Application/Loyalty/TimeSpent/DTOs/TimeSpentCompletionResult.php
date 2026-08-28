<?php

namespace App\Application\Loyalty\TimeSpent\DTOs;

use App\Domain\Loyalty\Models\LoyaltyTimeSpentSession;
use App\Domain\Loyalty\Models\PointLedgerEntry;

final readonly class TimeSpentCompletionResult
{
    public function __construct(
        public LoyaltyTimeSpentSession $session,
        public int $earnedPoints = 0,
        public ?PointLedgerEntry $entry = null,
    ) {}
}
