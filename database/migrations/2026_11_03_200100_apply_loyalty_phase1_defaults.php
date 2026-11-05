<?php

use App\Domain\Loyalty\Models\EarningRule;
use App\Infrastructure\Services\Setting\Groups\AffiliateSettings;
use App\Infrastructure\Services\Setting\Groups\GallerySettings;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * اقتصاد امتیاز فاز ۱: خرید وزن بیشتر، فعالیت روزانه وزن کمتر.
 *
 * فقط مقدارهایی عوض می‌شوند که هنوز همان پیش‌فرض قبلی‌اند؛ هر عددی که مدیر
 * خودش تغییر داده دست‌نخورده می‌ماند (همه از پنل قابل ویرایش‌اند).
 */
return new class extends Migration
{
    private const OLD_STREAK = [
        ['days' => 7, 'points' => 200],
        ['days' => 14, 'points' => 300],
        ['days' => 30, 'points' => 1000],
    ];

    public const NEW_STREAK = [
        ['days' => 7, 'points' => 50, 'repeat' => true],
        ['days' => 30, 'points' => 500, 'repeat' => true],
        ['days' => 60, 'points' => 0, 'vip_days' => 7, 'repeat' => false],
        ['days' => 90, 'points' => 0, 'vip_days' => 30, 'repeat' => true],
    ];

    public function up(): void
    {
        if (Schema::hasTable('earning_rules')) {
            $this->updateRule('purchase', ['points' => 10, 'amount_step' => 10000], ['points' => 20]);
            $this->updateRule('daily_visit', ['points' => 20], ['points' => 10]);
            $this->updateRule('comment', ['points' => 100, 'daily_limit' => 3], ['points' => 50, 'daily_limit' => 2]);
        }

        if (! Schema::hasTable('settings')) {
            return;
        }

        $loyalty = app(LoyaltySettings::class);
        $streak = $loyalty->get('streak_milestones');
        if ($streak === null || $streak === [] || $this->sameMilestones($streak, self::OLD_STREAK)) {
            $loyalty->set('streak_milestones', self::NEW_STREAK);
        }

        $affiliate = app(AffiliateSettings::class);
        if (in_array($affiliate->get('referral_bonus_referrer_points'), [null, 1000], true)) {
            $affiliate->set('referral_bonus_referrer_points', 200);
        }
        if ($affiliate->get('referral_sale_points_per_1000') === null) {
            $affiliate->set('referral_sale_points_per_1000', 2);
        }

        $gallery = app(GallerySettings::class);
        foreach (['image_points' => [150, 300], 'video_points' => [500, 800]] as $key => [$old, $new]) {
            $current = $gallery->get($key);
            if ($current === null || (int) $current === $old) {
                $gallery->set($key, $new);
            }
        }
    }

    public function down(): void
    {
        // داده‌ای حذف نمی‌شود؛ مقادیر از پنل قابل برگرداندن‌اند.
    }

    /**
     * @param array<string, int> $expected
     * @param array<string, int> $changes
     */
    private function updateRule(string $type, array $expected, array $changes): void
    {
        EarningRule::query()->where('type', $type)->get()->each(function (EarningRule $rule) use ($expected, $changes): void {
            $config = (array) ($rule->config ?? []);
            foreach ($expected as $key => $value) {
                if ((int) ($config[$key] ?? -1) !== $value) {
                    return;
                }
            }
            $rule->forceFill(['config' => array_merge($config, $changes)])->save();
        });
    }

    private function sameMilestones(mixed $current, array $old): bool
    {
        if (! is_array($current) || count($current) !== count($old)) {
            return false;
        }
        foreach (array_values($current) as $index => $item) {
            if ((int) ($item['days'] ?? 0) !== $old[$index]['days'] || (int) ($item['points'] ?? 0) !== $old[$index]['points']) {
                return false;
            }
        }

        return true;
    }
};
