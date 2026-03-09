<?php

use App\Infrastructure\Services\Date\DateConverterService;

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
        try {
            $settingsClass = '\\App\\Infrastructure\\Services\\Setting\\Groups\\' . ucfirst($group) . 'Settings';
            $settings = App::make($settingsClass);
            return $settings->get($key, $default);
        } catch (Throwable $e) {
            Log::info($e->getMessage());
            return null;
        }
    }
}
