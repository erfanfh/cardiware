<?php

use App\Domain\Loyalty\Models\EarningRule;
use App\Infrastructure\Services\Setting\Groups\GallerySettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * بازتنظیم اقتصاد امتیاز: فعالیت‌های بدون خرید (رسانه، دیدگاه، مأموریت‌های رایگان)
 * دیگر نباید کاربر را در چند روز چند سطح بالا ببرند. خرید محور اصلی رشد می‌ماند.
 *
 * فقط مقدارهایی عوض می‌شوند که هنوز پیش‌فرض قبلی‌اند؛ عددی که مدیر دستی تغییر داده دست نمی‌خورد.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            $gallery = app(GallerySettings::class);
            foreach ([
                'image_points' => [[150, 300], 80],
                'video_points' => [[500, 800], 200],
                'monthly_rewarded_limit' => [[5], 3],
            ] as $key => [$olds, $new]) {
                $current = $gallery->get($key);
                if ($current === null || in_array((int) $current, $olds, true)) {
                    $gallery->set($key, $new);
                }
            }
        }

        if (Schema::hasTable('earning_rules')) {
            EarningRule::query()->where('type', 'comment')->get()->each(function (EarningRule $rule): void {
                $config = (array) ($rule->config ?? []);
                if (in_array((int) ($config['points'] ?? -1), [50, 100], true)) {
                    $rule->forceFill(['config' => array_merge($config, ['points' => 30, 'daily_limit' => min(2, (int) ($config['daily_limit'] ?? 2))])])->save();
                }
            });
        }

        $this->lower('loyalty_missions', 'weekly_visits_3', 100, 50);
        $this->lower('loyalty_missions', 'weekly_comment_1', 150, 60);
        $this->lower('loyalty_achievements', 'streak_7', 200, 100);
        $this->lower('loyalty_achievements', 'comments_10', 200, 100);
    }

    public function down(): void
    {
        // مقادیر از پنل قابل برگرداندن‌اند.
    }

    private function lower(string $table, string $code, int $old, int $new): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)->where('code', $code)->where('reward_points', $old)->update(['reward_points' => $new]);
    }
};
