<?php

namespace App\Domain\Security\Enums;

enum AbuseEventTypeEnum: string
{
    case RATE_LIMITED = 'rate_limited';
    case AUTO_BLOCKED = 'auto_blocked';
}
