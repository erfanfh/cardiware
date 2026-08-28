<?php

namespace App\Domain\Loyalty\Enums;

enum LoyaltyTargetTypeEnum: string
{
    case PRODUCT = 'product';
    case POST = 'post';
}
