<?php

namespace App\Infrastructure\Support\Counters;

use Illuminate\Support\Facades\Redis;

class BufferedCounter
{
    public static function increment(
        string     $namespace,
        string     $type,
        int|string $id,
        int        $amount = 1
    ): void
    {
        Redis::incrby(self::counterKey($namespace, $type, $id), $amount);

        Redis::sadd(
            self::dirtyKey($namespace),
            self::dirtyValue($type, $id)
        );
    }

    public static function popDirty(string $namespace): ?string
    {
        $value = Redis::spop(self::dirtyKey($namespace));

        return $value ? (string)$value : null;
    }

    public static function pull(
        string     $namespace,
        string     $type,
        int|string $id
    ): int
    {
        $key = self::counterKey($namespace, $type, $id);

        $value = (int)Redis::get($key);

        if ($value > 0) {
            Redis::del($key);
        }

        return $value;
    }

    public static function get(
        string     $namespace,
        string     $type,
        int|string $id
    ): int
    {
        return (int)Redis::get(self::counterKey($namespace, $type, $id));
    }

    public static function parseDirtyValue(string $value): array
    {
        [$type, $id] = explode(':', $value, 2);

        return [$type, (int)$id];
    }

    private static function counterKey(
        string     $namespace,
        string     $type,
        int|string $id
    ): string
    {
        return "counter:{$namespace}:{$type}:{$id}";
    }

    private static function dirtyKey(string $namespace): string
    {
        return "counter:{$namespace}:dirty";
    }

    private static function dirtyValue(string $type, int|string $id): string
    {
        return "{$type}:{$id}";
    }
}
