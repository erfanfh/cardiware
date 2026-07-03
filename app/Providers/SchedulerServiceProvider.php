<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class SchedulerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Schedule $schedule): void
    {
        $viewsSchedule = max((int)config('cache.page_views_schedule', 1), 1);
        $viewsLimit = (int)config('cache.page_views_flush_limit', 10000);

        $downloadClicksSchedule = max((int)config('cache.download_clicks_schedule', 1), 1);
        $downloadClicksLimit = (int)config('cache.download_clicks_flush_limit', 10000);

        $schedule->command("app:transfer-views-to-database --limit={$viewsLimit}")
            ->cron("*/{$viewsSchedule} * * * *")
            ->withoutOverlapping();

        $schedule->command("app:transfer-download-clicks-to-database --limit={$downloadClicksLimit}")
            ->cron("*/{$downloadClicksSchedule} * * * *")
            ->withoutOverlapping();

        $schedule->command('coupons:expire-reservations')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }
}
