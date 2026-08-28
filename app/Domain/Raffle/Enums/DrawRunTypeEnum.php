<?php

namespace App\Domain\Raffle\Enums;

enum DrawRunTypeEnum: string
{
    case INITIAL = 'initial';
    case REDRAW = 'redraw';

    public function label(): string
    {
        return match ($this) {
            self::INITIAL => 'قرعه کشی اصلی',
            self::REDRAW => 'قرعه کشی مجدد',
        };
    }
}
