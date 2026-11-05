<?php

namespace App\Infrastructure\Support\Counters;

use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * شمارنده‌ی بافرشده در Redis (مثلاً بازدید صفحه‌ها) که دوره‌ای به دیتابیس منتقل می‌شود.
 *
 * شمارش بازدید نباید هیچ صفحه‌ای را از کار بیندازد: اگر Redis در دسترس نباشد
 * (سرور Redis خاموش، محیط محلی بدون Redis) افزایش شمارنده نادیده گرفته می‌شود و
 * خواندن آن صفر برمی‌گرداند؛ خطا فقط یک‌بار در هر درخواست گزارش می‌شود.
 */
class BufferedCounter
{
    private static bool $failureReported = false;

    public static function increment(
        string     $namespace,
        string     $type,
        int|string $id,
        int        $amount = 1
    ): void
    {
        try {
            Redis::incrby(self::counterKey($namespace, $type, $id), $amount);

            Redis::sadd(
                self::dirtyKey($namespace),
                self::dirtyValue($type, $id)
            );
        } catch (Throwable $exception) {
            self::reportOnce($exception);
        }
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
        try {
            return (int)Redis::get(self::counterKey($namespace, $type, $id));
        } catch (Throwable $exception) {
            self::reportOnce($exception);

            return 0;
        }
    }

    private static function reportOnce(Throwable $exception): void
    {
        if (self::$failureReported) {
            return;
        }

        self::$failureReported = true;
        report($exception);
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
