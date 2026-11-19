<?php

namespace App\Console\Commands;

use App\Infrastructure\Services\Setting\Groups\NotificationsSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * پاک‌سازی ماهانهٔ اعلان‌های قدیمی کاربران:
 * اعلان‌ها (مثل پاسخ به دیدگاه) به‌مرور فضای دیتابیس را اشغال می‌کنند.
 * این دستور اعلان‌های قدیمی‌تر از N روز (پیش‌فرض ۳۰ روز) را حذف می‌کند.
 *
 * زمان‌بندی: روز اول هر ماه ساعت ۳ بامداد.
 */
class CleanupOldUserNotificationsCommand extends Command
{
    protected $signature = 'notifications:cleanup-old
                            {--days= : تعداد روز نگهداری (پیش‌فرض از تنظیمات یا ۳۰)}';

    protected $description = 'حذف اعلان‌های قدیمی کاربران برای آزادسازی فضای دیتابیس (ماهانه)';

    public function handle(): int
    {
        $settings = app(NotificationsSettings::class);
        $days = filter_var(
            $this->option('days') ?? $settings->get('cleanup_days', 30),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );
        if ($days === false) {
            $this->error('تعداد روز نگهداری باید یک عدد صحیح مثبت باشد.');

            return self::INVALID;
        }

        $cutoff = now()->subDays($days);

        $count = DB::table('user_notifications')
            ->where('created_at', '<', $cutoff)
            ->delete();

        if ($count === 0) {
            $this->info("No notifications older than {$days} days to delete.");

            return self::SUCCESS;
        }

        $this->info("Deleted {$count} user notifications older than {$days} days.");

        return self::SUCCESS;
    }
}
