<?php

namespace App\Domain\Loyalty\Enums;

enum PointEntryTypeEnum: string
{
    case EARN = 'earn';
    case SPEND = 'spend';
    case REVERSAL = 'reversal';
    case EXPIRATION = 'expiration';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::EARN => 'کسب امتیاز',
            self::SPEND => 'مصرف امتیاز',
            self::REVERSAL => 'برگشت تراکنش',
            self::EXPIRATION => 'انقضای امتیاز',
            self::ADJUSTMENT => 'اصلاح دستی',
        };
    }

    public function supportsDirection(PointEntryDirectionEnum $direction): bool
    {
        return match ($this) {
            self::EARN => $direction->isCredit(),
            self::SPEND,
            self::EXPIRATION => $direction->isDebit(),
            self::REVERSAL,
            self::ADJUSTMENT => true,
        };
    }
}
