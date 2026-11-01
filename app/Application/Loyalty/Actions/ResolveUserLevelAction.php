<?php

namespace App\Application\Loyalty\Actions;

use App\Domain\Loyalty\Models\LoyaltyLevel;
use App\Domain\Loyalty\Models\PointAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * تعیین سطح فعلی کاربر از روی lifetime_xp.
 *
 * سطوح از جدول loyalty_levels خوانده می‌شوند و کوتاه‌مدت کش می‌شوند تا
 * هر رندر صفحه به کوئری اضافه نیاز نداشته باشد.
 */
final class ResolveUserLevelAction
{
    private const CACHE_KEY = 'loyalty:levels:active';

    private const CACHE_TTL_SECONDS = 300;

    /**
     * @return LoyaltyLevel سطح فعلی؛ اگر سطحی تعریف نشده باشد سطح پیش‌فرض ساختاری برمی‌گردد.
     */
    public function execute(PointAccount|int|null $accountOrXp): LoyaltyLevel
    {
        $xp = $accountOrXp instanceof PointAccount
            ? (int) $accountOrXp->lifetime_xp
            : (int) $accountOrXp;

        $levels = $this->activeLevels();

        if ($levels->isEmpty()) {
            return $this->fallbackLevel();
        }

        $current = $levels->first();

        foreach ($levels as $level) {
            if ($xp >= $level->min_xp) {
                $current = $level;
            }
        }

        return $current;
    }

    /**
     * سطح جانشین برای وقتی هیچ سطحی در جدول تعریف نشده است
     * (مثل محیط تست با دیتابیس خالی) تا صفحات بدون خطا رندر شوند.
     */
    private function fallbackLevel(): LoyaltyLevel
    {
        /** @var LoyaltyLevel $instance */
        $instance = (new LoyaltyLevel())->forceFill([
            'id' => 0,
            'title' => 'تازه‌وارد',
            'badge' => null,
            'min_xp' => 0,
            'reward_points' => 0,
            'purchase_multiplier' => 1.0,
            'color_key' => 'brand',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return $instance;
    }

    /**
     * سطح بعدی (برای نوار پیشرفت) یا null در بالاترین سطح.
     */
    public function nextLevel(PointAccount|int|null $accountOrXp): ?LoyaltyLevel
    {
        $xp = $accountOrXp instanceof PointAccount
            ? (int) $accountOrXp->lifetime_xp
            : (int) $accountOrXp;

        return $this->activeLevels()
            ->firstWhere('min_xp', '>', $xp);
    }

    /**
     * درصد پیشرفت از ابتدای سطح فعلی تا آستانه سطح بعد (۰..۱۰۰). در بالاترین سطح ۱۰۰.
     */
    public function progressPercent(PointAccount|int|null $accountOrXp): int
    {
        $account = $accountOrXp;
        $current = $this->execute($account);
        $next = $this->nextLevel($account);

        if ($next === null) {
            return 100;
        }

        $xp = $account instanceof PointAccount ? (int) $account->lifetime_xp : (int) $account;
        $span = max(1, $next->min_xp - $current->min_xp);

        return max(0, min(100, (int) round((($xp - $current->min_xp) / $span) * 100)));
    }

    /**
     * @return Collection<int, LoyaltyLevel>
     */
    public function activeLevels(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            static fn () => LoyaltyLevel::query()->active()->get()->values(),
        );
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
