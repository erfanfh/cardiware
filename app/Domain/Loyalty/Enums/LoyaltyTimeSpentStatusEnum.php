<?php

namespace App\Domain\Loyalty\Enums;

enum LoyaltyTimeSpentStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
}
