<?php

namespace Database\Seeders\Loyalty;

use App\Domain\Loyalty\Enums\AchievementMetricEnum;
use App\Domain\Loyalty\Models\LoyaltyAchievement;
use Illuminate\Database\Seeder;

/**
 * نشان‌های متنوع باشگاه (idempotent با کلید code) — مکمل نشان‌های پایه:
 * نظرسنجی، حضور روزانه، کلکسیون محصولات، گفتگو و حمایت مالی.
 */
class LoyaltyDiverseAchievementsSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'code' => 'polls_5',
                'title' => 'رأی‌دهندهٔ فعال',
                'description' => 'در ۵ نظرسنجی سایت شرکت کرده‌اید؛ نظر شما شکل‌دهندهٔ آیندهٔ Cardiware است.',
                'badge' => '🗳️',
                'metric' => AchievementMetricEnum::POLL_VOTES_COUNT,
                'threshold' => 5,
                'reward_points' => 100,
                'sort_order' => 30,
            ],
            [
                'code' => 'polls_20',
                'title' => 'صدای جامعه',
                'description' => 'در ۲۰ نظرسنجی شرکت کرده‌اید؛ یکی از تاثیرگذارترین اعضای هستید.',
                'badge' => '🗣️',
                'metric' => AchievementMetricEnum::POLL_VOTES_COUNT,
                'threshold' => 20,
                'reward_points' => 300,
                'sort_order' => 31,
            ],
            [
                'code' => 'visits_30',
                'title' => 'حضور پایدار',
                'description' => '۳۰ روز از سایت بازدید کرده‌اید؛ ثبات شما افتخار باشگاه است.',
                'badge' => '📅',
                'metric' => AchievementMetricEnum::DAILY_VISIT_DAYS,
                'threshold' => 30,
                'reward_points' => 200,
                'sort_order' => 32,
            ],
            [
                'code' => 'visits_100',
                'title' => 'حضور طلایی',
                'description' => '۱۰۰ روز بازدید روزانه؛ شما بخشی از خانوادهٔ Cardiware هستید.',
                'badge' => '🏅',
                'metric' => AchievementMetricEnum::DAILY_VISIT_DAYS,
                'threshold' => 100,
                'reward_points' => 500,
                'sort_order' => 33,
            ],
            [
                'code' => 'products_5',
                'title' => 'کلکسیونر',
                'description' => 'مالک ۵ محصول مختلف Cardiware هستید.',
                'badge' => '🎮',
                'metric' => AchievementMetricEnum::PRODUCTS_OWNED,
                'threshold' => 5,
                'reward_points' => 300,
                'sort_order' => 34,
            ],
            [
                'code' => 'comments_50',
                'title' => 'استاد گفتگو',
                'description' => '۵۰ دیدگاه تاییدشده؛ دانسته‌های شما راهگشای بقیهٔ گیمرها بوده است.',
                'badge' => '💬',
                'metric' => AchievementMetricEnum::APPROVED_COMMENTS_COUNT,
                'threshold' => 50,
                'reward_points' => 400,
                'sort_order' => 35,
            ],
            [
                'code' => 'spend_10m',
                'title' => 'حامی ویژه',
                'description' => 'مجموع خرید شما از ۱۰ میلیون تومان گذشته است؛ از حمایت شما سپاسگزاریم.',
                'badge' => '💎',
                'metric' => AchievementMetricEnum::TOTAL_PURCHASE_AMOUNT,
                'threshold' => 10000000,
                'reward_points' => 500,
                'sort_order' => 36,
            ],
            [
                'code' => 'gallery_media_1',
                'title' => 'هنرمند گالری',
                'description' => 'اولین رسانه شما در گالری کاربران تایید شده است.',
                'badge' => '🎨',
                'metric' => AchievementMetricEnum::APPROVED_MEDIA_SUBMISSIONS,
                'threshold' => 1,
                'reward_points' => 100,
                'sort_order' => 37,
            ],
            [
                'code' => 'referral_1',
                'title' => 'اولین دعوت موفق',
                'description' => 'یک دوست با لینک شما ثبت‌نام کرده است.',
                'badge' => '🤝',
                'metric' => AchievementMetricEnum::REFERRAL_SIGNUPS,
                'threshold' => 1,
                'reward_points' => 200,
                'sort_order' => 40,
            ],
            [
                'code' => 'referral_5',
                'title' => 'مربی تیم',
                'description' => 'پنج دوست با لینک شما ثبت‌نام کرده‌اند.',
                'badge' => '🎯',
                'metric' => AchievementMetricEnum::REFERRAL_SIGNUPS,
                'threshold' => 5,
                'reward_points' => 500,
                'sort_order' => 41,
            ],
            [
                'code' => 'referral_10',
                'title' => 'لیدر جامعه',
                'description' => 'ده دوست با لینک شما ثبت‌نام کرده‌اند.',
                'badge' => '👑',
                'metric' => AchievementMetricEnum::REFERRAL_SIGNUPS,
                'threshold' => 10,
                'reward_points' => 1200,
                'sort_order' => 42,
            ],
        ];

        foreach ($achievements as $achievement) {
            LoyaltyAchievement::query()->updateOrCreate(
                ['code' => $achievement['code']],
                array_merge($achievement, ['is_active' => true]),
            );
        }
    }
}
