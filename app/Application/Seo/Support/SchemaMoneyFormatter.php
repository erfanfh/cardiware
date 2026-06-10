<?php

namespace App\Application\Seo\Support;

final class SchemaMoneyFormatter
{
    public function tomanToIrr(int|float|null $amount): int
    {
        if ($amount === null) {
            return 0;
        }

        return (int)round($amount * 10);
    }

    public function currency(): string
    {
        return 'IRR';
    }
}
