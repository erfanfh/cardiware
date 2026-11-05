<?php

namespace App\Infrastructure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;

class ArrayCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null)
            return [];

        if (is_array($value))
            return $value;

        try {
            // JSON_THROW_ON_ERROR خطا را خودش پرتاب می‌کند و json_last_error() را ریست نمی‌کند؛
            // بررسی json_last_error() اینجا خطای کهنه‌ی یک decode قبلی در همان درخواست را
            // می‌دید و مقدار سالم را نامعتبر اعلام می‌کرد (خطای ۵۰۰ در لندینگ‌ها).
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : [];
        } catch (JsonException $e) {
            throw new InvalidArgumentException("Value for '$key' is not a valid JSON array: " . $e->getMessage(), previous: $e);
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null)
            return null;

        if (!is_array($value))
            throw new InvalidArgumentException("Value for '$key' must be an array.");

        try {
            return self::encodeOrScrub($value, $key);
        } catch (JsonException $e) {
            throw new InvalidArgumentException("Could not encode array to JSON for key '$key': " . $e->getMessage(), previous: $e);
        }
    }

    /**
     * کدگذاری JSON؛ اگر مقدار حاوی UTF-8 خراب باشد (مثلاً متن جای‌گذاری‌شده از
     * منبع ناسالم) درست‌سازی و دوباره تلاش می‌شود تا ذخیره با خطای 500 متوقف نشود.
     */
    private static function encodeOrScrub(array $value, string $key): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException) {
            return json_encode(self::scrubUtf8($value), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }
    }

    private static function scrubUtf8(mixed $value): mixed
    {
        if (is_array($value)) {
            $scrubbed = [];

            foreach ($value as $k => $v) {
                $scrubbed[is_string($k) ? self::scrubString($k) : $k] = self::scrubUtf8($v);
            }

            return $scrubbed;
        }

        return is_string($value) ? self::scrubString($value) : $value;
    }

    private static function scrubString(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // توالی‌های بایت نامعتبر یا حذف یا با نویسه جایگزین عوض می‌شوند.
        $scrubbed = mb_scrub($value, 'UTF-8');

        if ($scrubbed !== '' && mb_check_encoding($scrubbed, 'UTF-8')) {
            return $scrubbed;
        }

        $iconv = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return $iconv === false ? '' : $iconv;
    }
}
