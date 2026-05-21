<?php

namespace App\Domain\Marketplace\Enums;

enum StoreStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
