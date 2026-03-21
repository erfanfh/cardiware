<?php

use App\Infrastructure\Services\Date\DateConverterService;
use Carbon\Carbon;

if (!function_exists('jalali_date')) {

    /**
     * Convert a Gregorian date to a Jalali formatted date.
     *
     * @param DateTimeInterface|string|int|null $date
     * @param string|null $format Format of the output Jalali date (optional)
     * @param string|null $inputFormat Format of the input date string (optional)
     * @return string
     */
    function jalali_date(
        DateTimeInterface|string|int|null $date,
        ?string                           $format = null,
        ?string                           $inputFormat = null,
    ): string
    {
        $converter = app(DateConverterService::class);

        return $converter->toJalali(
            date: $date,
            outputFormat: $format,
            inputFormat: $inputFormat,
        );
    }
}

if (!function_exists('gregorian_date')) {

    /**
     * Convert a Jalali date to a Gregorian formatted date.
     *
     * @param string|int|null $jalaliDate
     * @param string|null $format Format of the output Gregorian date (optional)
     * @param string|null $inputFormat Format of the input Jalali date string (optional)
     * @return string
     */
    function gregorian_date(
        string|int|null $jalaliDate,
        ?string         $format = null,
        ?string         $inputFormat = null,
    ): string
    {
        $converter = app(DateConverterService::class);

        return $converter->toGregorian(
            jalaliDate: $jalaliDate,
            outputFormat: $format,
            inputFormat: $inputFormat,
        );
    }
}

if (!function_exists('time_ago')) {
    /**
     * Convert a datetime value to a human-readable Persian relative time.
     *
     * @param mixed $datetime Supported: Carbon instance, datetime string, timestamp
     * @param bool $full Whether to show multiple parts, e.g. "1 سال و 2 ماه پیش"
     * @return string|null
     */
    function time_ago(mixed $datetime, bool $full = false): ?string
    {
        if (blank($datetime)) {
            return null;
        }

        try {
            $now = Carbon::now();

            if ($datetime instanceof Carbon) {
                $time = $datetime->copy();
            } elseif (is_numeric($datetime)) {
                $time = Carbon::createFromTimestamp((int)$datetime);
            } else {
                $time = Carbon::parse($datetime);
            }
        } catch (Throwable $e) {
            return null;
        }

        if ($time->equalTo($now)) {
            return 'همین الان';
        }

        $isFuture = $time->greaterThan($now);
        $diff = $now->diff($time);

        $years = (int)$diff->y;
        $months = (int)$diff->m;
        $weeks = intdiv((int)$diff->d, 7);
        $days = (int)$diff->d % 7;
        $hours = (int)$diff->h;
        $minutes = (int)$diff->i;
        $seconds = (int)$diff->s;

        $parts = [];

        if ($years > 0) {
            $parts[] = $years . ' سال';
        }

        if ($months > 0) {
            $parts[] = $months . ' ماه';
        }

        if ($weeks > 0) {
            $parts[] = $weeks . ' هفته';
        }

        if ($days > 0) {
            $parts[] = $days . ' روز';
        }

        if ($hours > 0) {
            $parts[] = $hours . ' ساعت';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . ' دقیقه';
        }

        if ($seconds > 0) {
            $parts[] = $seconds . ' ثانیه';
        }

        if (empty($parts)) {
            return 'همین الان';
        }

        if (!$full) {
            $parts = array_slice($parts, 0, 1);
        }

        $text = implode(' و ', $parts);

        return $isFuture
            ? $text . ' دیگر'
            : $text . ' پیش';
    }
}

if (!function_exists('normalize_number')) {
    /**
     * @param string|null $number
     * @return string|null
     */
    function normalize_number(?string $number): string|null
    {
        if ($number === null) {
            return null;
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($persian, $english, str_replace($arabic, $english, $number));
    }
}

if (!function_exists('get_setting')) {
    /**
     * Returns value of the given setting
     *
     * @param string $group
     * @param string $key
     * @param mixed $default
     * @return null
     */
    function get_setting(string $group, string $key, mixed $default = null): mixed
    {
        static $instances = [];

        try {
            $settingsClass = '\\App\\Infrastructure\\Services\\Setting\\Groups\\' . ucfirst($group) . 'Settings';

            if (!class_exists($settingsClass))
                return $default;

            if (!isset($instances[$settingsClass]))
                $instances[$settingsClass] = App::make($settingsClass);

            return $instances[$settingsClass]->get($key, $default);
        } catch (Throwable $e) {
            Log::warning('Failed to resolve setting.', [
                'group' => $group,
                'key' => $key,
                'message' => $e->getMessage(),
            ]);

            return $default;
        }
    }
}
