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
