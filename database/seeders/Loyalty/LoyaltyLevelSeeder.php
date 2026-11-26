<?php

namespace Database\Seeders\Loyalty;

use App\Domain\Loyalty\Models\LoyaltyLevel;
use Illuminate\Database\Seeder;

/**
 * سطوح هفت‌گانه باشگاه مخاطبان Cardiware.
 * جزو DatabaseSeeder اصلی نیست تا نصب پروداکشن مقادیر خودش را از پنل تنظیم کند؛
 * این سیدر فقط سطوح پیش‌فرض پیشنهادی را می‌سازد (idempotent با firstOrCreate).
 */
class LoyaltyLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['title' => 'تازه‌وارد', 'badge' => '🌱', 'min_xp' => 0, 'reward_points' => 0, 'purchase_multiplier' => 1.00, 'color_key' => 'slate', 'sort_order' => 1],
            ['title' => 'بازیکن', 'badge' => '🎮', 'min_xp' => 2000, 'reward_points' => 200, 'purchase_multiplier' => 1.05, 'color_key' => 'emerald', 'sort_order' => 2],
            ['title' => 'حرفه‌ای', 'badge' => '⚡', 'min_xp' => 5000, 'reward_points' => 400, 'purchase_multiplier' => 1.10, 'color_key' => 'cyan', 'sort_order' => 3],
            ['title' => 'ستاره', 'badge' => '⭐', 'min_xp' => 10000, 'reward_points' => 750, 'purchase_multiplier' => 1.15, 'color_key' => 'brand', 'sort_order' => 4],
            ['title' => 'قهرمان', 'badge' => '🏆', 'min_xp' => 20000, 'reward_points' => 1000, 'purchase_multiplier' => 1.20, 'color_key' => 'amber', 'sort_order' => 5],
            ['title' => 'اسطوره', 'badge' => '👑', 'min_xp' => 40000, 'reward_points' => 1500, 'purchase_multiplier' => 1.30, 'color_key' => 'violet', 'sort_order' => 6],
            ['title' => 'MW ELITE', 'badge' => '💎', 'min_xp' => 75000, 'reward_points' => 2500, 'purchase_multiplier' => 1.50, 'color_key' => 'rose', 'sort_order' => 7],
        ];

        foreach ($levels as $level) {
            LoyaltyLevel::query()->firstOrCreate(
                ['title' => $level['title']],
                $level,
            );
        }
    }
}
