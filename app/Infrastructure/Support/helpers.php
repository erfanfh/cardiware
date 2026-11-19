<?php

use App\Application\Seo\Support\CanonicalUrlGenerator;
use App\Application\Seo\Support\SeoManager;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Domain\System\Models\PageView;
use App\Infrastructure\Services\Date\DateConverterService;
use App\Infrastructure\Support\Counters\BufferedCounter;
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

if (!function_exists('page_views')) {
    function page_views(PageViewableInterface $model): int
    {
        $modelClass = class_basename($model);

        $storedViews = Cache::tags([
            CacheTag::PAGE_VIEWS,
            CacheTag::pageViews($modelClass, $model->id),
        ])->remember(
            CacheKey::storedPageViews($modelClass, $model->id),
            now()->addMinutes(config('cache.page_views_lifetime')),
            function () use ($model) {
                return (int)PageView::query()
                    ->where('pageable_type', $model::class)
                    ->where('pageable_id', $model->id)
                    ->value('count');
            }
        );

        $bufferedViews = BufferedCounter::get(
            namespace: 'page_views',
            type: $modelClass,
            id: $model->id,
        );

        return $storedViews + $bufferedViews;
    }
}

if (!function_exists('public_page_views')) {
    /**
     * Return the public-facing view count without changing stored analytics.
     */
    function public_page_views(PageViewableInterface $model): int
    {
        $offset = max(0, (int)($model->display_views_offset ?? 0));

        return page_views($model) + $offset;
    }
}

if (!function_exists('first_filled')) {
    function first_filled(...$values): string
    {
        foreach ($values as $value) {
            $value = trim((string)($value ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('persian_number')) {
    /**
     * فرمت عدد با ارقام فارسی و جداکنندهٔ هزارگان فارسی.
     * جایگزین number_format برای نمایش یکدست اعداد در UI فارسی.
     */
    function persian_number(int|float|string|null $number): string
    {
        if ($number === null) {
            return '۰';
        }

        $formatted = number_format((float)$number);
        $formatted = str_replace(',', '٬', $formatted);
        $formatted = str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            $formatted
        );

        return $formatted;
    }
}

if (!function_exists('approximate_view')) {
    function approximate_view(int $number): string
    {
        if ($number < 100)
            return '0';
        elseif ($number < 1000)
            return '+' . (floor($number / 100) * 100);
        elseif ($number < 1000000)
            return '+' . (floor($number / 1000)) . 'K';
        else
            return '+1M';
    }
}

if (!function_exists('seo_head')) {
    function seo_head(): string
    {
        return app(SeoManager::class)->render();
    }
}


if (!function_exists('front_route')) {
    function front_route(
        string $name,
        mixed  $parameters = [],
        bool   $absolute = true,
        bool   $forceTrailingSlash = true,
    ): string
    {
        return app(CanonicalUrlGenerator::class)->generate(
            route($name, $parameters, $absolute),
            $forceTrailingSlash
        );
    }
}
