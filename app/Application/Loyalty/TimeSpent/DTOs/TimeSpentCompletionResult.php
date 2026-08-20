<?php

namespace App\Application\Loyalty\TimeSpent\DTOs;

use App\Domain\Loyalty\Models\LoyaltyTimeSpentSession;

final readonly class TimeSpentCompletionResult
{
    public function __construct(
        public LoyaltyTimeSpentSession $session,
        public int $earnedPoints = 0,
    ) {
    }
}
