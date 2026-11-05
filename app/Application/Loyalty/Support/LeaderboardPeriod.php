<?php

namespace App\Application\Loyalty\Support;

use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;

/**
 * دوره‌ی رقابت جدول برترین‌ها به تقویم شمسی: ماه، فصل، سال یا کل دوران.
 * در هر دوره رتبه با XP کسب‌شده در همان دوره تعیین می‌شود (خرج کردن امتیاز
 * رتبه را پایین نمی‌آورد؛ مرجوعی و لغو امتیاز آن را کم می‌کند).
 */
final readonly class LeaderboardPeriod
{
    public const KEYS = ['week', 'month', 'season', 'year', 'all'];

    private const SEASONS = [1 => 'بهار', 2 => 'تابستان', 3 => 'پاییز', 4 => 'زمستان'];

    private function __construct(
        public string $key,
        public string $label,
        public ?Carbon $startsAt,
        public ?Carbon $endsAt,
        // شناسه‌ی پایدار دوره برای ثبت برندگان: 1405-08 / 1405-Q3 / 1405
        public string $code = 'all',
    ) {
    }

    /** دوره‌ی قبلی از همین نوع (برای بستن ماه/فصل/سالی که تمام شده). */
    public function previous(): self
    {
        if ($this->startsAt === null) {
            return $this;
        }

        return self::make($this->key, $this->startsAt->copy()->subDay()->setTime(12, 0));
    }

    public static function make(?string $key, ?Carbon $at = null): self
    {
        $key = in_array($key, self::KEYS, true) ? $key : 'month';
        $tz = new \DateTimeZone(config('app.timezone'));
        $now = Jalalian::fromCarbon(($at ?? now())->copy()->setTimezone($tz));

        if ($key === 'all') {
            return new self('all', 'کل دوران', null, null);
        }

        if ($key === 'week') {
            // هفته‌ی ایرانی از شنبه شروع می‌شود (Carbon: شنبه = 6).
            $local = ($at ?? now())->copy()->setTimezone($tz);
            $start = Carbon::instance($local)->startOfDay()->subDays(($local->dayOfWeek + 1) % 7);
            $end = $start->copy()->addDays(7);
            $jStart = Jalalian::fromCarbon($start);

            return new self('week', 'هفته ' . $jStart->format('%d %B %Y'), $start, $end, 'W' . $start->format('Y-m-d'));
        }

        [$first, $months, $label, $code] = match ($key) {
            'season' => [
                $now->getFirstDayOfQuarter(), 3,
                'فصل ' . self::SEASONS[$now->getQuarter()] . ' ' . $now->getYear(),
                $now->getYear() . '-Q' . $now->getQuarter(),
            ],
            'year' => [$now->getFirstDayOfYear(), 12, 'سال ' . $now->getYear(), (string) $now->getYear()],
            default => [
                $now->getFirstDayOfMonth(), 1, $now->format('%B %Y'),
                $now->getYear() . '-' . str_pad((string) $now->getMonth(), 2, '0', STR_PAD_LEFT),
            ],
        };

        $start = Carbon::instance($first->toCarbon())->startOfDay();
        $end = Carbon::instance($first->addMonths($months)->toCarbon())->startOfDay();

        return new self($key, $label, $start, $end, $code);
    }

    /** @return array<string, string> */
    public static function tabs(): array
    {
        return ['month' => 'این ماه', 'season' => 'این فصل', 'year' => 'امسال', 'all' => 'کل دوران'];
    }
}
