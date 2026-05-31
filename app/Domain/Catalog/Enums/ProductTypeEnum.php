<?php

namespace App\Domain\Catalog\Enums;

enum ProductTypeEnum: string
{
    case DIGITAL = 'digital';
    case PHYSICAL = 'physical';
}
