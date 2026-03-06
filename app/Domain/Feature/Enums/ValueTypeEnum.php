<?php

namespace App\Domain\Feature\Enums;

enum ValueTypeEnum: string
{
    case FLAG = 'flag';
    case BOOL = 'bool';
    case INT = 'int';
    case STRING = 'string';
}
