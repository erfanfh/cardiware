<?php

namespace App\Domain\Identity\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super-admin';
    case STUDENT = 'student';

    public function label(): string
    {
        return match ($this) {
            RoleEnum::SUPER_ADMIN => 'ادمین کل',
            RoleEnum::STUDENT => 'دانشجو'
        };
    }
}
