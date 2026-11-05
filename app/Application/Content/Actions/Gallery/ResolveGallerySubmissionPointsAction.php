<?php

namespace App\Application\Content\Actions\Gallery;

use App\Domain\Content\Models\UserMediaSubmission;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;

/**
 * امتیاز رسانه‌ی تأییدشده‌ی کاربران، به‌صورت کاهشی و با سقف ماهانه تا رقابت عادلانه بماند:
 *  - اولین رسانه‌ی امتیازدار کاربر: امتیاز کامل (تصویر/ویدیو از تنظیمات گالری)
 *  - دومی: second_points_percent درصد (پیش‌فرض ۵۰)
 *  - سومی به بعد: later_points_percent درصد (پیش‌فرض ۲۵)
 *  - در هر ماه شمسی حداکثر monthly_rewarded_limit رسانه امتیاز می‌گیرد (پیش‌فرض ۳).
 */
final class ResolveGallerySubmissionPointsAction
{
    /** @return array{points: int, tier: int, limit_reached: bool} */
    public function execute(UserMediaSubmission $submission): array
    {
        $base = (int) get_setting('gallery', $submission->type->value === 'video' ? 'video_points' : 'image_points', $submission->type->value === 'video' ? 200 : 80);

        $rewardedBefore = UserMediaSubmission::query()
            ->where('user_id', $submission->user_id)
            ->whereKeyNot($submission->getKey())
            ->where('points_awarded', '>', 0)
            ->count();

        $percent = match (true) {
            $rewardedBefore === 0 => 100,
            $rewardedBefore === 1 => (int) get_setting('gallery', 'second_points_percent', 50),
            default => (int) get_setting('gallery', 'later_points_percent', 25),
        };

        $limit = (int) get_setting('gallery', 'monthly_rewarded_limit', 3);
        $rewardedThisMonth = UserMediaSubmission::query()
            ->where('user_id', $submission->user_id)
            ->whereKeyNot($submission->getKey())
            ->where('points_awarded', '>', 0)
            ->where('approved_at', '>=', self::monthStart())
            ->count();
        $limitReached = $limit > 0 && $rewardedThisMonth >= $limit;

        return [
            'points' => $limitReached ? 0 : (int) round(max(0, $base) * max(0, min(100, $percent)) / 100),
            'tier' => min(3, $rewardedBefore + 1),
            'limit_reached' => $limitReached,
        ];
    }

    public static function monthStart(): Carbon
    {
        return Carbon::instance(
            Jalalian::now(new \DateTimeZone(config('app.timezone')))->getFirstDayOfMonth()->toCarbon()->startOfDay()
        );
    }
}
