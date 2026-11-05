<?php

namespace App\Console\Commands;

use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Application\Marketplace\SellerRewards\CloseSellerPeriodAction;
use App\Infrastructure\Services\Setting\Groups\SellerRewardSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * هر روز هفته/ماه/سالِ تمام‌شده را برای فروشندگان می‌بندد و به سه فروشگاه برتر جایزه می‌دهد.
 * دوره‌هایی که پیش از راه‌اندازی این قابلیت تمام شده‌اند جایزه نمی‌گیرند.
 */
class CloseSellerRewardPeriodsCommand extends Command
{
    protected $signature = 'marketplace:close-seller-periods';

    protected $description = 'Close finished seller weeks/months/years and reward the top three stores';

    public function handle(CloseSellerPeriodAction $close, SellerRewardSettings $settings): int
    {
        if (! $settings->enabled()) {
            $this->info('Seller rewards are disabled.');

            return self::SUCCESS;
        }

        $since = $settings->get('enabled_since');
        if (blank($since)) {
            $since = now()->toIso8601String();
            $settings->set('enabled_since', $since);
        }
        $enabledSince = Carbon::parse($since);

        foreach (['week', 'month', 'year'] as $type) {
            $period = LeaderboardPeriod::make($type)->previous();
            if ($period->endsAt->lte($enabledSince)) {
                continue;
            }

            try {
                $closure = $close->execute($period);
                if ($closure !== null) {
                    $this->info("{$period->label}: {$closure->winners_count} store(s)");
                }
            } catch (Throwable $exception) {
                report($exception);
                $this->error("{$period->label}: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
