<?php

namespace App\Application\Marketplace\SellerRewards;

use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Application\Notification\SendInAppNotification;
use App\Domain\Marketplace\Models\SellerPeriodClosure;
use App\Domain\Marketplace\Models\StoreReward;
use App\Infrastructure\Services\Setting\Groups\SellerRewardSettings;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

/**
 * بستن هفته/ماه/سالِ تمام‌شده برای فروشندگان: سه فروشگاه با بیشترین فروش خالص (و
 * حداقل فروش تعیین‌شده) جایزه می‌گیرند. هر دوره فقط یک‌بار بسته می‌شود.
 */
final class CloseSellerPeriodAction
{
    public function __construct(
        private readonly GetSellerRankingAction $ranking,
        private readonly GrantStoreRewardAction $grant,
        private readonly SellerRewardSettings $settings,
    ) {
    }

    /** @throws Throwable */
    public function execute(LeaderboardPeriod $period): ?SellerPeriodClosure
    {
        if (! in_array($period->key, ['week', 'month', 'year'], true)) {
            throw new InvalidArgumentException('Seller rewards close weeks, months or years only.');
        }

        if ($period->endsAt->isFuture()
            || SellerPeriodClosure::query()->where('period_type', $period->key)->where('period_key', $period->code)->exists()) {
            return null;
        }

        $minimum = $this->settings->minNetSales($period->key);
        $winners = $this->ranking->execute($period, 20)
            ->filter(fn (object $row): bool => $row->net_sales >= $minimum)
            ->take(3)
            ->values();

        $closure = DB::transaction(function () use ($period, $winners): ?SellerPeriodClosure {
            $closure = SellerPeriodClosure::query()->firstOrCreate(
                ['period_type' => $period->key, 'period_key' => $period->code],
                [
                    'period_label' => $period->label,
                    'starts_at' => $period->startsAt,
                    'ends_at' => $period->endsAt,
                    'closed_at' => now(),
                    'winners_count' => $winners->count(),
                ],
            );

            if (! $closure->wasRecentlyCreated) {
                return null;
            }

            $prizes = $this->settings->prizes($period->key);
            foreach ($winners as $index => $row) {
                $reward = StoreReward::query()->create([
                    'closure_id' => $closure->getKey(),
                    'store_id' => $row->store_id,
                    'period_type' => $period->key,
                    'period_key' => $period->code,
                    'period_label' => $period->label,
                    'rank' => $index + 1,
                    'net_sales' => $row->net_sales,
                    'orders_count' => $row->orders_count,
                ]);

                $this->grant->execute($reward, $prizes[$index] ?? null);
            }

            return $closure;
        });

        if ($closure !== null) {
            foreach ($closure->rewards()->with('store.sellers')->get() as $reward) {
                rescue(fn () => app(SendInAppNotification::class)->sellerReward($reward), report: true);
            }
        }

        return $closure;
    }
}
