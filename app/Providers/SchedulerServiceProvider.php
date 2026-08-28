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
        $viewsSchedule = max((int) config('cache.page_views_schedule', 1), 1);
        $viewsLimit = (int) config('cache.page_views_flush_limit', 10000);

        $downloadClicksSchedule = max((int) config('cache.download_clicks_schedule', 1), 1);
        $downloadClicksLimit = (int) config('cache.download_clicks_flush_limit', 10000);

        $schedule->command("app:transfer-views-to-database --limit={$viewsLimit}")
            ->cron("*/{$viewsSchedule} * * * *")
            ->withoutOverlapping();

        $schedule->command("app:transfer-download-clicks-to-database --limit={$downloadClicksLimit}")
            ->cron("*/{$downloadClicksSchedule} * * * *")
            ->withoutOverlapping();

        $schedule->command('wallet-holds:expire')
            ->everyMinute()
            ->withoutOverlapping(5)
            ->onOneServer();

        $schedule->command('coupons:expire-reservations')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('tickets:auto-close-inactive')
            ->hourlyAt(10)
            ->withoutOverlapping(30)
            ->onOneServer();

        $schedule->command('carts:cleanup-stale')
            ->dailyAt('03:30')
            ->withoutOverlapping(120)
            ->onOneServer();

        $schedule->command('raffles:sync-statuses')
            ->everyMinute()
            ->withoutOverlapping(5)
            ->onOneServer();

        $schedule->command('raffles:expire-overdue-winners')
            ->everyFiveMinutes()
            ->withoutOverlapping(10)
            ->onOneServer();

        $schedule->command('raffles:send-winner-reminders')
            ->everyFiveMinutes()
            ->withoutOverlapping(10)
            ->onOneServer();
    }
}
