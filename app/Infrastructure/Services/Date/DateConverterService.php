<?php

namespace App\Infrastructure\Services\Date;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;

class DateConverterService
{
    private string $defaultOutputFormat;

    private ?string $defaultInputFormat;

    /**
     * @param string|null $defaultOutputFormat Default Jalali output format
     * @param string|null $defaultInputFormat Default input format (for string dates)
     */
    public function __construct(
        ?string $defaultOutputFormat = null,
        ?string $defaultInputFormat = null,
    )
    {
        $this->defaultOutputFormat = $defaultOutputFormat ?? 'Y/m/d H:i';
        $this->defaultInputFormat = $defaultInputFormat; // null = auto-parse by Carbon
    }

    /**
     * Convert Gregorian date to Jalali.
     *
     * @param DateTimeInterface|string|int|null $date
     * @param string|null $outputFormat Desired Jalali format (null => use default)
     * @param string|null $inputFormat Format of input string (null => auto parse)
     * @return string
     */
    public function toJalali(
        DateTimeInterface|string|int|null $date,
        ?string                           $outputFormat = null,
        ?string                           $inputFormat = null,
    ): string
    {
        if ($date === null)
            return '';

        $outputFormat = $outputFormat ?? $this->defaultOutputFormat;
        $inputFormat = $inputFormat ?? $this->defaultInputFormat;

        if ($date instanceof DateTimeInterface)
            $carbon = Carbon::instance($date);
        elseif (is_int($date))
            $carbon = Carbon::createFromTimestamp($date);
        elseif ($inputFormat !== null)
            $carbon = Carbon::createFromFormat($inputFormat, $date);
        else
            $carbon = Carbon::parse($date);

        return Jalalian::fromCarbon($carbon)->format($outputFormat);
    }

    /**
     * Convert Jalali date to Gregorian string.
     *
     * @param string|int|null $jalaliDate
     * @param string|null $outputFormat Desired Gregorian format (null => use default)
     * @param string|null $inputFormat Jalali input format (null => use default)
     * @return string
     */
    public function toGregorian(
        string|int|null $jalaliDate,
        ?string         $outputFormat = null,
        ?string         $inputFormat = null,
    ): string
    {
        if ($jalaliDate === null)
            return '';

        $outputFormat = $outputFormat ?? $this->defaultOutputFormat;
        $inputFormat = $inputFormat ?? $this->defaultInputFormat ?? 'Y/m/d H:i';

        if (is_int($jalaliDate)) {
            // Timestamp Jalali (rare, but safe)
            return Carbon::createFromTimestamp($jalaliDate)->format($outputFormat);
        }

        $jalalian = Jalalian::fromFormat($inputFormat, $jalaliDate);

        return $jalalian->toCarbon()->format($outputFormat);
    }
}
