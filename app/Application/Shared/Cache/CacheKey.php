<?php

namespace App\Application\Shared\Cache;

final class CacheKey
{
    public static function settingsGroup(string $group): string
    {
        return "settings:group:{$group}";
    }
}
