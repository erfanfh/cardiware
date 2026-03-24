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
        $time = (int)config('cache.page_views_schedule');

        $schedule->command('app:transfer-views-to-database')
            ->cron("*/$time * * * *")
            ->withoutOverlapping();
    }
}
