<?php

namespace App\Application\Shared\Cache;

use DateTimeInterface;

final class CacheTtl
{
    public static function short(): DateTimeInterface
    {
        return now()->addMinutes(10);
    }

    public static function medium(): DateTimeInterface
    {
        return now()->addMinutes(30);
    }

    public static function long(): DateTimeInterface
    {
        return now()->addHours(12);
    }

    public static function daily(): DateTimeInterface
    {
        return now()->addDay();
    }
}
