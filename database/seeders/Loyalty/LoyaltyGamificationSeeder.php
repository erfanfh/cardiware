<?php

namespace Database\Seeders\Loyalty;

use App\Domain\Loyalty\Models\LoyaltyAchievement;
use App\Domain\Loyalty\Models\LoyaltyMission;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Illuminate\Database\Seeder;

/**
 * داده‌های پیش‌فرض گیمیفیکیشن باشگاه: مأموریت‌های هفتگی، نشان‌ها،
 * مایل‌استون‌های زنجیره ورود و تنظیم انقضا.
 * idempotent است و در نصب پروداکشن اجرایش اختیاری/قابل ویرایش از پنل است.
 */
class LoyaltyGamificationSeeder extends Seeder
{
    public function run(): void
    {
        $missions = [
            ['code' => 'weekly_visits_3', 'title' => 'سه روز این هفته سر بزن', 'description' => 'در ۳ روز مختلفِ این هفته از مودینگ‌وی بازدید کن.', 'icon' => '🔥', 'metric' => 'daily_visits', 'target_count' => 3, 'reward_points' => 100, 'sort_order' => 1],
            ['code' => 'weekly_comment_1', 'title' => 'یک دیدگاه مفید بنویس', 'description' => 'یک دیدگاه تأییدشده روی مطالب یا محصولات ثبت کن.', 'icon' => '💬', 'metric' => 'approved_comments', 'target_count' => 1, 'reward_points' => 150, 'sort_order' => 2],
            ['code' => 'weekly_purchase_1', 'title' => 'اولین خرید هفته', 'description' => 'این هفته یک سفارش پرداخت‌شده ثبت کن.', 'icon' => '🛍️', 'metric' => 'paid_orders', 'target_count' => 1, 'reward_points' => 300, 'sort_order' => 3],
        ];

        foreach ($missions as $mission) {
            LoyaltyMission::query()->firstOrCreate(
                ['code' => $mission['code']],
                $mission,
            );
        }

        $achievements = [
            ['code' => 'first_purchase', 'title' => 'اولین خرید', 'description' => 'اولین سفارش موفق خود را ثبت کن.', 'badge' => '🛒', 'metric' => 'paid_orders_count', 'threshold' => 1, 'reward_points' => 500, 'sort_order' => 1],
            ['code' => 'orders_5', 'title' => '۵ خرید', 'description' => 'پنج سفارش پرداخت‌شده داشته باش.', 'badge' => '🛍️', 'metric' => 'paid_orders_count', 'threshold' => 5, 'reward_points' => 300, 'sort_order' => 2],
            ['code' => 'orders_10', 'title' => '۱۰ خرید', 'description' => 'ده سفارش پرداخت‌شده داشته باش.', 'badge' => '⭐', 'metric' => 'paid_orders_count', 'threshold' => 10, 'reward_points' => 750, 'sort_order' => 3],
            ['code' => 'big_spender_5m', 'title' => 'خرید ۵ میلیونی', 'description' => 'مجموع خریدهایت از ۵ میلیون تومان بگذرد.', 'badge' => '💎', 'metric' => 'total_purchase_amount', 'threshold' => 5_000_000, 'reward_points' => 1000, 'sort_order' => 4],
            ['code' => 'streak_7', 'title' => 'هفته آتشین', 'description' => '۷ روز متوالی از سایت بازدید کن.', 'badge' => '🔥', 'metric' => 'longest_streak_days', 'threshold' => 7, 'reward_points' => 200, 'sort_order' => 5],
            ['code' => 'comments_10', 'title' => 'ده دیدگاه', 'description' => 'ده دیدگاه تأییدشده ثبت کن.', 'badge' => '💬', 'metric' => 'approved_comments_count', 'threshold' => 10, 'reward_points' => 200, 'sort_order' => 6],
            ['code' => 'member_365', 'title' => 'عضو قدیمی', 'description' => 'یک سال کامل عضو مودینگ‌وی باش.', 'badge' => '👑', 'metric' => 'membership_days', 'threshold' => 365, 'reward_points' => 0, 'sort_order' => 7],
        ];

        foreach ($achievements as $achievement) {
            LoyaltyAchievement::query()->firstOrCreate(
                ['code' => $achievement['code']],
                $achievement,
            );
        }

        $loyalty = app(LoyaltySettings::class);
        $loyalty->set('streak_milestones', [
            ['days' => 7, 'points' => 200],
            ['days' => 14, 'points' => 300],
            ['days' => 30, 'points' => 1000],
        ]);
        $loyalty->set('points_inactivity_expiry_months', 12);
    }
}
