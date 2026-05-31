<?php

namespace App\Domain\Catalog\Enums;

enum ProductStructureEnum: string
{
    case SIMPLE = 'simple';
    case VARIABLE = 'variable';
}
