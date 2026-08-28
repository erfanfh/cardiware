<?php

namespace App\Domain\Raffle\Enums;

enum DrawWinnerKindEnum: string
{
    case PRIMARY = 'primary';
    case RESERVE = 'reserve';

    public function label(): string
    {
        return match ($this) {
            self::PRIMARY => 'برنده اصلی',
            self::RESERVE => 'برنده ذخیره',
        };
    }
}
