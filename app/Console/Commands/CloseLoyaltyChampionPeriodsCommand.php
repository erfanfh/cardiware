<?php

namespace App\Console\Commands;

use App\Application\Loyalty\Champions\CloseChampionPeriodAction;
use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * هر روز اجرا می‌شود و ماه/فصل/سالی را که تمام شده می‌بندد و به برترین‌ها جایزه می‌دهد.
 * دوره‌هایی که پیش از راه‌اندازی این قابلیت تمام شده‌اند جایزه نمی‌گیرند.
 */
class CloseLoyaltyChampionPeriodsCommand extends Command
{
    protected $signature = 'loyalty:close-champion-periods';

    protected $description = 'Close finished loyalty months/seasons/years and reward the top three members';

    public function handle(CloseChampionPeriodAction $close, LoyaltySettings $settings): int
    {
        $since = $settings->get('champions_enabled_since');
        if (blank($since)) {
            $since = now()->toIso8601String();
            $settings->set('champions_enabled_since', $since);
        }
        $enabledSince = Carbon::parse($since);

        foreach (['month', 'season', 'year'] as $type) {
            $period = LeaderboardPeriod::make($type)->previous();

            if ($period->endsAt === null || $period->endsAt->lte($enabledSince)) {
                continue;
            }

            try {
                $closure = $close->execute($period);
                if ($closure !== null) {
                    $this->info("{$period->label}: {$closure->winners_count} winner(s)");
                }
            } catch (Throwable $exception) {
                report($exception);
                $this->error("{$period->label}: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
