<?php

namespace App\Application\Loyalty\Support;

use App\Domain\Identity\Models\User;
use Carbon\CarbonInterface;

/**
 * پروفایل اختصاصی VIP در کارت عمومی باشگاه: تم رنگی، معرفی کوتاه و تاریخ شروع VIP.
 * معرفی و تم فقط تا وقتی اشتراک فعال است نمایش داده می‌شوند (ذخیره‌شده باقی می‌مانند).
 */
final class VipClubProfile
{
    public const BIO_MAX = 160;

    /** @var array<string, array{label: string, header: string, ring: string, chip: string}> */
    public const THEMES = [
        'gold' => [
            'label' => 'طلایی',
            'header' => 'from-amber-300 via-amber-500 to-orange-500',
            'ring' => 'ring-amber-400',
            'chip' => 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300',
        ],
        'violet' => [
            'label' => 'بنفش سلطنتی',
            'header' => 'from-violet-400 via-fuchsia-500 to-pink-500',
            'ring' => 'ring-fuchsia-400',
            'chip' => 'border-fuchsia-300 bg-fuchsia-50 text-fuchsia-800 dark:border-fuchsia-500/40 dark:bg-fuchsia-500/10 dark:text-fuchsia-300',
        ],
        'emerald' => [
            'label' => 'زمردی',
            'header' => 'from-emerald-300 via-teal-500 to-cyan-600',
            'ring' => 'ring-emerald-400',
            'chip' => 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-300',
        ],
        'ocean' => [
            'label' => 'اقیانوسی',
            'header' => 'from-sky-300 via-blue-500 to-indigo-600',
            'ring' => 'ring-sky-400',
            'chip' => 'border-sky-300 bg-sky-50 text-sky-800 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-300',
        ],
        'night' => [
            'label' => 'شب',
            'header' => 'from-slate-700 via-slate-900 to-black',
            'ring' => 'ring-slate-500',
            'chip' => 'border-slate-300 bg-slate-100 text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200',
        ],
    ];

    public static function theme(?string $key): array
    {
        return self::THEMES[$key ?? ''] ?? self::THEMES['gold'];
    }

    public static function themeKey(?string $key): string
    {
        return isset(self::THEMES[$key ?? '']) ? $key : 'gold';
    }

    /** شروع اشتراک‌های پیوسته‌ی فعلی (برای «VIP از …»). */
    public static function activeSince(User $user): ?CarbonInterface
    {
        $subscriptions = $user->vipSubscriptions()
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', now())
            ->orderByDesc('expires_at')
            ->get(['starts_at', 'expires_at']);

        $current = $subscriptions->first(fn ($s) => $s->expires_at->isFuture());
        if ($current === null) {
            return null;
        }

        // اشتراک‌های پشت‌سرهم (تمدید) یک دوره حساب می‌شوند.
        $since = $current->starts_at;
        foreach ($subscriptions as $subscription) {
            if ($subscription->expires_at->greaterThanOrEqualTo($since->copy()->subDay()) && $subscription->starts_at->lessThan($since)) {
                $since = $subscription->starts_at;
            }
        }

        return $since;
    }
}
