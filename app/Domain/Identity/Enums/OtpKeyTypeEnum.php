<?php

namespace App\Domain\Identity\Enums;

enum OtpKeyTypeEnum: string
{
    case MOBILE = 'mobile';
    case EMAIL = 'email';
}
