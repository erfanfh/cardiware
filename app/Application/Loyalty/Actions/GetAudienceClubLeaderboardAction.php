<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * جدول برترین‌های باشگاه.
 *
 * رتبه بر اساس XP کسب‌شده در دوره (ماه/فصل/سال شمسی) است، نه موجودی قابل خرج:
 * کاربری که امتیازش را برای جایزه خرج می‌کند نباید رتبه‌اش را از دست بدهد. امتیاز
 * برگشت‌خورده (مرجوعی، دیدگاه ردشده) از همان دوره کم می‌شود. کارمندان سایت در
 * رقابت حضور ندارند.
 */
class GetAudienceClubLeaderboardAction
{
    public const LEADERBOARD_LIMIT = 50;

    /** کش جدول باشگاه و رتبه‌ی هر عضو (۱۰ دقیقه). */
    public const CACHE_SECONDS = 600;

    /** reason‌هایی که XP نمی‌دهند (پاداش سطح و نشان). */
    private const NON_XP_REASONS = ['level_up', 'achievement_earned'];

    /** @return array<string, mixed> */
    public function execute(?User $user, ?string $period = 'month'): array
    {
        $period = LeaderboardPeriod::make($period);

        $public = app(PublicReadCache::class)->remember(
            'leaderboard', ['period' => $period->key, 'start' => $period->startsAt?->toDateString()],
            [PointAccount::class, User::class, MediaFile::class],
            fn () => $this->publicData($period),
            seconds: self::CACHE_SECONDS,
        );

        $currentAccount = $user?->pointAccount()->first();
        $isStaff = $user?->isLoyaltyStaff() ?? false;
        // امتیاز و رتبه‌ی هر عضو دو کوئری تجمیعی روی کل دفتر امتیازند؛ ۱۰ دقیقه کش
        // (هم‌اندازه‌ی کش جدول عمومی) تا رفرش‌های پشت‌سرهم بار سرور نشوند.
        [$score, $currentRank] = $currentAccount
            ? \Illuminate\Support\Facades\Cache::remember(
                "loyalty-leaderboard-member:{$period->key}:{$period->code}:{$currentAccount->getKey()}",
                self::CACHE_SECONDS,
                function () use ($currentAccount, $period, $isStaff): array {
                    $score = $this->scoreFor($currentAccount, $period);

                    return [$score, $score > 0 && ! $isStaff ? $this->rankFor($currentAccount, $score, $period) : null];
                },
            )
            : [0, null];

        return [
            ...$public,
            'period' => $period,
            'periods' => LeaderboardPeriod::tabs(),
            'currentMember' => [
                'rank' => $currentRank,
                'score' => $score,
                'balance' => max(0, (int) ($currentAccount?->balance ?? 0)),
                'lifetime_earned' => (int) ($currentAccount?->lifetime_earned ?? 0),
                'lifetime_xp' => (int) ($currentAccount?->lifetime_xp ?? 0),
                'is_visible' => $currentRank !== null && $currentRank <= self::LEADERBOARD_LIMIT,
                'is_staff' => $isStaff,
            ],
        ];
    }

    /**
     * فهرست رتبه‌بندی‌شده‌ی دوره بدون کش (برای بستن دوره و انتخاب برترین‌ها).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, PointAccount>
     */
    public function rankedAccounts(LeaderboardPeriod $period, int $limit = self::LEADERBOARD_LIMIT): \Illuminate\Database\Eloquent\Collection
    {
        return $this->rankedQuery($period)->with('user')->limit($limit)->get();
    }

    private function publicData(LeaderboardPeriod $period): array
    {
        $leaders = $this->rankedQuery($period)
            ->with(['user' => fn ($query) => $query->withActiveVipFlag(), 'user.avatar'])
            ->limit(self::LEADERBOARD_LIMIT)
            ->get()
            ->values();

        $leaders->each(function (PointAccount $account, int $index): void {
            $account->setAttribute('leaderboard_rank', $index + 1);
            $account->setAttribute('leaderboard_score', (int) $account->getAttribute('leaderboard_score'));
        });

        $scores = DB::query()->fromSub($this->scoredAccountsQuery($period)->toBase(), 'ranked');
        $totalMembers = (int) (clone $scores)->count();
        $totalPoints = (int) (clone $scores)->sum('leaderboard_score');

        return [
            'leaders' => $leaders,
            'visibleLimit' => self::LEADERBOARD_LIMIT,
            'stats' => [
                'members' => $totalMembers,
                'total_points' => $totalPoints,
                'average_points' => $totalMembers > 0 ? (int) floor($totalPoints / $totalMembers) : 0,
                'highest_points' => (int) ($leaders->first()?->leaderboard_score ?? 0),
            ],
        ];
    }

    /** @return Builder<PointAccount> */
    private function rankedQuery(LeaderboardPeriod $period): Builder
    {
        return $this->scoredAccountsQuery($period)
            ->orderByDesc('leaderboard_score')
            ->orderByDesc('point_accounts.lifetime_xp')
            ->orderBy('point_accounts.id');
    }

    /**
     * حساب‌های رتبه‌دار همراه ستون leaderboard_score.
     *
     * @return Builder<PointAccount>
     */
    private function scoredAccountsQuery(LeaderboardPeriod $period): Builder
    {
        $query = PointAccount::query()
            ->whereHas('user', function (Builder $query): void {
                $query->where('status', UserStatusEnum::ACTIVE->value)->withoutLoyaltyStaff();
            });

        if ($period->startsAt === null) {
            return $query
                ->select('point_accounts.*')
                ->selectRaw('point_accounts.lifetime_xp as leaderboard_score')
                ->where('point_accounts.lifetime_xp', '>', 0);
        }

        return $query
            ->joinSub($this->periodScores($period), 'period_scores', 'period_scores.point_account_id', '=', 'point_accounts.id')
            ->select('point_accounts.*')
            ->selectRaw('period_scores.score as leaderboard_score')
            ->where('period_scores.score', '>', 0);
    }

    /**
     * XP هر حساب در دوره: امتیازهای XP‌دار کسب‌شده در دوره، منهای هر مقداری از
     * همان ردیف‌ها که بعداً برگشت خورده، به‌اضافه/منهای اصلاح دستی XP مدیر.
     */
    private function periodScores(LeaderboardPeriod $period): QueryBuilder
    {
        $reversed = DB::table('point_ledger_entries')
            ->selectRaw('reversal_of_id, SUM(amount) as reversed_amount')
            ->whereNotNull('reversal_of_id')
            ->groupBy('reversal_of_id');

        return DB::table('point_ledger_entries as e')
            ->leftJoinSub($reversed, 'r', 'r.reversal_of_id', '=', 'e.id')
            ->where(fn(QueryBuilder $query) => $query
                ->where(fn(QueryBuilder $earn) => $earn
                    ->where('e.type', 'earn')
                    ->whereNotIn('e.reason', self::NON_XP_REASONS))
                ->orWhere(fn(QueryBuilder $adjust) => $adjust
                    ->where('e.type', 'adjustment')
                    ->where('e.reason', 'manual_xp_adjustment')))
            ->where('e.occurred_at', '>=', $period->startsAt)
            ->where('e.occurred_at', '<', $period->endsAt)
            ->groupBy('e.point_account_id')
            ->selectRaw('e.point_account_id')
            ->selectRaw("SUM(CASE WHEN e.direction = 'debit' THEN -e.amount ELSE e.amount - COALESCE(r.reversed_amount, 0) END) as score");
    }

    private function scoreFor(PointAccount $account, LeaderboardPeriod $period): int
    {
        if ($period->startsAt === null) {
            return max(0, (int) $account->lifetime_xp);
        }

        return max(0, (int) DB::query()
            ->fromSub($this->periodScores($period), 's')
            ->where('point_account_id', $account->getKey())
            ->value('score'));
    }

    private function rankFor(PointAccount $account, int $score, LeaderboardPeriod $period): int
    {
        $ahead = DB::query()
            ->fromSub($this->scoredAccountsQuery($period)->toBase(), 'ranked')
            ->where(function (QueryBuilder $query) use ($account, $score): void {
                $query
                    ->where('leaderboard_score', '>', $score)
                    ->orWhere(fn(QueryBuilder $tie) => $tie
                        ->where('leaderboard_score', $score)
                        ->where('lifetime_xp', '>', (int) $account->lifetime_xp))
                    ->orWhere(fn(QueryBuilder $tie) => $tie
                        ->where('leaderboard_score', $score)
                        ->where('lifetime_xp', (int) $account->lifetime_xp)
                        ->where('id', '<', $account->getKey()));
            })
            ->count();

        return $ahead + 1;
    }
}
