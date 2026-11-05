<?php

namespace App\Application\Loyalty\Actions;

use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;

/**
 * پاداش اشتراک ویژه در باشگاه مخاطبان: کاربر VIP از هر امتیازی که از قوانین
 * کسب امتیاز می‌گیرد، درصدی بیشتر دریافت می‌کند (پنل مدیریت ← تنظیمات باشگاه).
 * چون XP سطح با همان امتیاز بالا می‌رود، کاربر VIP سریع‌تر هم ارتقای سطح می‌گیرد.
 */
class ResolveVipPointsBonusAction
{
    public const MAX_PERCENT = 500;

    public function __construct(private readonly LoyaltySettings $settings)
    {
    }

    /** درصد پاداش تنظیم‌شده (۰ = خاموش). */
    public function percent(): int
    {
        return max(0, min(self::MAX_PERCENT, (int) $this->settings->get('vip_points_bonus_percent', 0)));
    }

    /** درصد پاداشی که همین الان به این کاربر تعلق می‌گیرد. */
    public function percentFor(User $user): int
    {
        $percent = $this->percent();

        return $percent > 0 && $user->hasActiveVipSubscription() ? $percent : 0;
    }

    public function apply(int $points, int $percent): int
    {
        if ($points <= 0 || $percent <= 0) {
            return $points;
        }

        return (int) round($points * (100 + $percent) / 100);
    }
}
