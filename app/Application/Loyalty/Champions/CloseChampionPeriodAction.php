<?php

namespace App\Application\Loyalty\Champions;

use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Application\Notification\SendInAppNotification;
use App\Domain\Commerce\Models\Order;
use App\Domain\Loyalty\Models\LoyaltyChampion;
use App\Domain\Loyalty\Models\LoyaltyPeriodClosure;
use App\Domain\Loyalty\Models\PointAccount;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

/**
 * بستن یک ماه/فصل/سال تمام‌شده: نفرات ۱ تا ۳ بر اساس XP همان دوره انتخاب و جایزه
 * می‌گیرند. هر دوره فقط یک‌بار بسته می‌شود (حتی بدون برنده).
 *
 * کاربر برتر سال باید حد نصاب سالانه را هم داشته باشد (XP، مأموریت کامل‌شده، خرید)؛
 * اگر نفری در جدول حد نصاب را ندارد، نفر بعدی جای او را می‌گیرد. نفر اول سال جایزه‌ی
 * ویژه (مثلاً کنسول) هم دارد که مدیر دستی تحویل می‌دهد.
 */
final class CloseChampionPeriodAction
{
    public function __construct(
        private readonly GetAudienceClubLeaderboardAction $leaderboard,
        private readonly GrantChampionPrizeAction $grantPrize,
        private readonly LoyaltySettings $settings,
    ) {
    }

    /** @throws Throwable */
    public function execute(LeaderboardPeriod $period): ?LoyaltyPeriodClosure
    {
        if ($period->startsAt === null || $period->endsAt === null) {
            throw new InvalidArgumentException('Only a month, season or year can be closed.');
        }

        if ($period->endsAt->isFuture()) {
            return null;
        }

        if (LoyaltyPeriodClosure::query()->where('period_type', $period->key)->where('period_key', $period->code)->exists()) {
            return null;
        }

        $winners = $this->pickWinners($period);

        $closure = DB::transaction(function () use ($period, $winners): ?LoyaltyPeriodClosure {
            $closure = LoyaltyPeriodClosure::query()->firstOrCreate(
                ['period_type' => $period->key, 'period_key' => $period->code],
                [
                    'period_label' => $period->label,
                    'starts_at' => $period->startsAt,
                    'ends_at' => $period->endsAt,
                    'closed_at' => now(),
                    'winners_count' => count($winners),
                ],
            );

            if (! $closure->wasRecentlyCreated) {
                return null;
            }

            $prizes = $this->settings->championPrizes($period->key);
            foreach ($winners as $index => $account) {
                $champion = LoyaltyChampion::query()->create([
                    'closure_id' => $closure->getKey(),
                    'period_type' => $period->key,
                    'period_key' => $period->code,
                    'period_label' => $period->label,
                    'rank' => $index + 1,
                    'user_id' => $account->user_id,
                    'score' => (int) $account->getAttribute('leaderboard_score'),
                    'grand_prize' => $period->key === 'year' && $index === 0
                        ? (trim((string) $this->settings->get('champion_year_grand_prize', '')) ?: null)
                        : null,
                ]);

                $this->grantPrize->execute($champion, $prizes[$index] ?? null);
            }

            return $closure;
        });

        if ($closure !== null) {
            foreach ($closure->champions()->get() as $champion) {
                rescue(fn () => app(SendInAppNotification::class)->loyaltyChampion($champion), report: true);
            }
        }

        return $closure;
    }

    /** @return list<PointAccount> */
    private function pickWinners(LeaderboardPeriod $period): array
    {
        $winners = [];
        foreach ($this->leaderboard->rankedAccounts($period, 100) as $account) {
            if ($account->user === null || (int) $account->getAttribute('leaderboard_score') <= 0) {
                continue;
            }
            if ($period->key === 'year' && ! $this->meetsYearThresholds($account, $period)) {
                continue;
            }
            $winners[] = $account;
            if (count($winners) === 3) {
                break;
            }
        }

        return $winners;
    }

    /** حد نصاب کاربر برتر سال: XP سال، مأموریت‌های کامل‌شده و تعداد خرید در همان سال. */
    public function meetsYearThresholds(PointAccount $account, LeaderboardPeriod $period): bool
    {
        $minXp = (int) $this->settings->get('champion_year_min_xp', 20000);
        $minMissions = (int) $this->settings->get('champion_year_min_missions', 20);
        $minOrders = (int) $this->settings->get('champion_year_min_orders', 3);

        if ((int) $account->getAttribute('leaderboard_score') < $minXp) {
            return false;
        }

        $missions = DB::table('loyalty_mission_progress')
            ->where('user_id', $account->user_id)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $period->startsAt)
            ->where('completed_at', '<', $period->endsAt)
            ->count();
        if ($missions < $minMissions) {
            return false;
        }

        $orders = Order::query()
            ->where('user_id', $account->user_id)
            ->paid()
            ->where('paid_at', '>=', $period->startsAt)
            ->where('paid_at', '<', $period->endsAt)
            ->count();

        return $orders >= $minOrders;
    }
}
