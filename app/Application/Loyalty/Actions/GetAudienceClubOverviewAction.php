<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\Missions\Actions\RecordMissionProgressAction;
use App\Application\Loyalty\PointConversion\Actions\ResolvePointConversionConfigAction;
use App\Application\Loyalty\Streak\Actions\RecordDailyStreakAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\LoyaltyAchievement;
use App\Domain\Loyalty\Models\LoyaltyMission;
use App\Domain\Loyalty\Models\LoyaltyStreak;
use App\Domain\Loyalty\Models\PointConversion;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class GetAudienceClubOverviewAction
{
    public function __construct(
        private readonly EvaluateRewardOfferAvailabilityAction $evaluateReward,
        private readonly ResolvePointConversionConfigAction $resolvePointConversionConfig,
        private readonly ResolveUserLevelAction $resolveUserLevel,
    ) {}

    /** @return array<string, mixed> */
    public function execute(User $user, ?string $historyFilter = null): array
    {
        $historyFilter = in_array($historyFilter, ['earned', 'spent'], true) ? $historyFilter : null;
        $account = $user->pointAccount()->first();
        $balance = (int) ($account?->balance ?? 0);
        $wallet = $user->wallet()->first();
        $conversionConfig = $this->resolvePointConversionConfig->execute();
        $convertedToday = $conversionConfig->isAvailable()
            ? (int) PointConversion::query()
                ->where('user_id', $user->getKey())
                ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                ->sum('points')
            : 0;
        $remainingDailyPoints = $conversionConfig->dailyPointsLimit > 0
            ? max(0, $conversionConfig->dailyPointsLimit - $convertedToday)
            : $balance;
        $maximumConvertiblePoints = max(0, $balance);

        if ($conversionConfig->maximumPointsPerRequest > 0) {
            $maximumConvertiblePoints = min(
                $maximumConvertiblePoints,
                $conversionConfig->maximumPointsPerRequest,
            );
        }

        if ($conversionConfig->dailyPointsLimit > 0) {
            $maximumConvertiblePoints = min($maximumConvertiblePoints, $remainingDailyPoints);
        }

        $maximumConvertiblePoints = $conversionConfig->floorToConversionUnit(
            $maximumConvertiblePoints,
        );

        $pointEntries = $this->pointEntries($account?->getKey(), $historyFilter);
        $vipBonus = app(ResolveVipPointsBonusAction::class);
        $isVip = $user->hasActiveVipSubscription();
        $lifetimeEarned = (int) ($account?->lifetime_earned ?? 0);
        $lifetimeXp = (int) ($account?->lifetime_xp ?? 0);
        $level = $this->resolveUserLevel->execute($account);
        $nextLevel = $this->resolveUserLevel->nextLevel($account);
        $levelColors = $level->colorClasses();
        $gamification = $this->gamification($user);
        $earningRules = EarningRule::query()
            ->currentlyActive()
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get()
            ->unique(fn (EarningRule $rule): string => $rule->type->value)
            ->values();

        $rewardOffers = RewardOffer::query()
            ->currentlyActive()
            ->with('target')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(function (RewardOffer $offer): bool {
                if ($offer->type !== RewardTypeEnum::RAFFLE_TICKET) {
                    return true;
                }

                $campaign = $offer->target;
                if (! $campaign) {
                    return false;
                }

                return in_array($campaign->status, [
                    DrawCampaignStatusEnum::SCHEDULED,
                    DrawCampaignStatusEnum::OPEN,
                ], true);
            })
            ->values();

        $rewardAvailabilities = $rewardOffers
            ->mapWithKeys(fn (RewardOffer $offer) => [
                $offer->getKey() => $this->evaluateReward->execute($offer, $user),
            ]);

        $rewardRedemptions = RewardRedemption::query()
            ->where('user_id', $user->getKey())
            ->with([
                'rewardOffer.target',
                'coupon',
                'fulfillment' => function (MorphTo $morphTo): void {
                    $morphTo->morphWith([
                        TicketGrant::class => ['campaign', 'tickets'],
                    ]);
                },
                'pointLedgerEntry',
                'reversalPointLedgerEntry',
            ])
            ->latest('id')
            ->paginate(8, ['*'], 'rewards_page')
            ->withQueryString();

        return [
            'pointAccount' => $account,
            'pointBalance' => $balance,
            'spendablePointBalance' => max(0, $balance),
            'lifetimeEarned' => $lifetimeEarned,
            'lifetimeSpent' => (int) ($account?->lifetime_spent ?? 0),
            'level' => [
                'model' => $level,
                'next' => $nextLevel,
                'title' => $level->title,
                'badge' => $level->badge,
                'number' => $this->resolveUserLevel->activeLevels()->search(fn ($l) => $l->is($level)) + 1,
                'xp' => $lifetimeXp,
                'multiplier' => (float) $level->purchase_multiplier,
                'colors' => $levelColors,
                'nextTitle' => $nextLevel?->title,
                'nextMin' => $nextLevel?->min_xp,
                'progressPercent' => $this->resolveUserLevel->progressPercent($account),
                'xpToNext' => $nextLevel ? max(0, $nextLevel->min_xp - $lifetimeXp) : 0,
                'ladder' => $this->resolveUserLevel->activeLevels(),
            ],
            'pointEntries' => $pointEntries,
            'historyFilter' => $historyFilter,
            'spendBreakdown' => $this->spendBreakdown($account?->getKey()),
            'vip' => [
                'isVip' => $isVip,
                'bonusPercent' => $vipBonus->percent(),
                'bonusEarned' => $this->vipBonusEarned($account?->getKey()),
                'since' => $isVip ? \App\Application\Loyalty\Support\VipClubProfile::activeSince($user) : null,
                'bio' => $user->club_bio,
                'theme' => \App\Application\Loyalty\Support\VipClubProfile::themeKey($user->club_theme),
            ],
            'monthlySummary' => $this->monthlySummary($account?->getKey(), $user),
            'streak' => $gamification['streak'],
            'missions' => $gamification['missions'],
            'achievements' => $gamification['achievements'],
            'earningRules' => $earningRules,
            'rewardOffers' => $rewardOffers,
            'rewardAvailabilities' => $rewardAvailabilities,
            'rewardRedemptions' => $rewardRedemptions,
            'completedRewardCount' => RewardRedemption::query()
                ->where('user_id', $user->getKey())
                ->where('status', RewardRedemptionStatusEnum::COMPLETED->value)
                ->count(),
            'pointConversion' => [
                'config' => $conversionConfig,
                'isEnabled' => $conversionConfig->isAvailable(),
                'canConvert' => $conversionConfig->isAvailable()
                    && $maximumConvertiblePoints >= $conversionConfig->minimumPoints
                    && (! $wallet || $wallet->allowsUserTransactions()),
                'convertedToday' => $convertedToday,
                'remainingDailyPoints' => $remainingDailyPoints,
                'maximumConvertiblePoints' => $maximumConvertiblePoints,
                'maximumWalletAmount' => $conversionConfig->isAvailable()
                    ? $conversionConfig->walletAmountFor($maximumConvertiblePoints)
                    : 0,
                'walletAvailableBalance' => (int) ($wallet?->available_balance ?? 0),
                'walletIsBlocked' => $wallet && ! $wallet->allowsUserTransactions(),
            ],
        ];
    }

    /**
     * امتیازهای مصرف‌شده به تفکیک کاربرد (مصرف‌های برگشت‌خورده حساب نمی‌شوند).
     *
     * @return list<array{key: string, label: string, points: int, count: int}>
     */
    private function spendBreakdown(?int $accountId): array
    {
        if (! $accountId) {
            return [];
        }

        $entries = PointLedgerEntry::query()
            ->where('point_account_id', $accountId)
            ->where('type', \App\Domain\Loyalty\Enums\PointEntryTypeEnum::SPEND->value)
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('point_ledger_entries as reversals')
                ->whereColumn('reversals.reversal_of_id', 'point_ledger_entries.id'))
            ->get(['id', 'reason', 'amount', 'reference_type', 'reference_id']);

        $offerTypes = RewardOffer::query()
            ->whereIn('id', $entries->where('reference_type', (new RewardOffer)->getMorphClass())->pluck('reference_id')->filter()->unique())
            ->pluck('type', 'id');

        $groups = [];
        foreach ($entries as $entry) {
            [$key, $label] = self::spendCategory($entry, $offerTypes->get($entry->reference_id));
            $groups[$key] ??= ['key' => $key, 'label' => $label, 'points' => 0, 'count' => 0];
            $groups[$key]['points'] += (int) $entry->amount;
            $groups[$key]['count']++;
        }

        usort($groups, fn (array $a, array $b): int => $b['points'] <=> $a['points']);

        return $groups;
    }

    /** @return array{0: string, 1: string} */
    public static function spendCategory(PointLedgerEntry $entry, mixed $offerType = null): array
    {
        $offerType = $offerType instanceof RewardTypeEnum ? $offerType : RewardTypeEnum::tryFrom((string) $offerType);

        return match (true) {
            $offerType === RewardTypeEnum::COUPON => ['coupon', 'خرید کد تخفیف'],
            $offerType === RewardTypeEnum::RAFFLE_TICKET => ['raffle', 'ثبت کد قرعه‌کشی'],
            $entry->reason === 'wallet_conversion' => ['wallet', 'تبدیل به موجودی کیف پول'],
            in_array($entry->reason, ['reward_redemption', 'reward_redeem'], true) => ['reward', 'دریافت پاداش'],
            default => ['other', 'سایر مصرف‌ها'],
        };
    }

    /** امتیاز اضافه‌ای که به‌خاطر VIP بودن گرفته شده. */
    private function vipBonusEarned(?int $accountId): int
    {
        if (! $accountId) {
            return 0;
        }

        return (int) PointLedgerEntry::query()
            ->where('point_account_id', $accountId)
            ->where('type', \App\Domain\Loyalty\Enums\PointEntryTypeEnum::EARN->value)
            ->where('metadata', 'like', '%vip_bonus_percent%')
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('point_ledger_entries as reversals')
                ->whereColumn('reversals.reversal_of_id', 'point_ledger_entries.id'))
            ->get(['amount', 'metadata'])
            ->sum(fn (PointLedgerEntry $entry): int => max(0, (int) $entry->amount - (int) data_get($entry->metadata, 'points_before_vip_bonus', $entry->amount)));
    }

    private function pointEntries(?int $accountId, ?string $filter = null): LengthAwarePaginator
    {
        if (! $accountId) {
            return PointLedgerEntry::query()
                ->whereRaw('1 = 0')
                ->paginate(12, ['*'], 'points_page')
                ->withQueryString();
        }

        return PointLedgerEntry::query()
            ->where('point_account_id', $accountId)
            ->when($filter === 'earned', fn ($query) => $query->where('direction', \App\Domain\Loyalty\Enums\PointEntryDirectionEnum::CREDIT->value))
            ->when($filter === 'spent', fn ($query) => $query->where('direction', \App\Domain\Loyalty\Enums\PointEntryDirectionEnum::DEBIT->value))
            ->with(['earningRule', 'reference', 'reversalOf'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(12, ['*'], 'points_page')
            ->withQueryString();
    }

    /**
     * جمع کسب و مصرف امتیاز برای چهار ماه اخیر.
     *
     * @param int|null $accountId
     * @param User $user
     * @return array<int, array{label:string, earned:int, spent:int}>
     */
    /**
     * داده‌های گیمیفیکیشن صفحه باشگاه: زنجیره، مأموریت‌های هفته و نشان‌ها.
     *
     * @param User $user
     * @return array{streak: array<string, mixed>, missions: \Illuminate\Support\Collection<int, array<string, mixed>>, achievements: array<string, mixed>}
     */
    private function gamification(User $user): array
    {
        $streakModel = LoyaltyStreak::query()
            ->where('user_id', $user->getKey())
            ->first();

        $milestones = app(RecordDailyStreakAction::class)->milestones();
        $currentDays = (int) ($streakModel?->current_days ?? 0);
        $nextMilestone = null;

        // نزدیک‌ترین پاداش بعدی؛ مایل‌استون تکرارشونده در مضرب بعدی‌اش می‌افتد.
        // پاداش‌هایی که در یک روز می‌افتند (مثلاً روز ۹۰: امتیاز + VIP) با هم نمایش داده می‌شوند.
        foreach ($milestones as $milestone) {
            $target = $milestone['repeat']
                ? (intdiv($currentDays, $milestone['days']) + 1) * $milestone['days']
                : $milestone['days'];

            if ($target <= $currentDays) {
                continue;
            }

            if ($nextMilestone === null || $target < $nextMilestone['days']) {
                $nextMilestone = ['days' => $target, 'points' => $milestone['points'], 'vip_days' => $milestone['vip_days']];
            } elseif ($target === $nextMilestone['days']) {
                $nextMilestone['points'] += $milestone['points'];
                $nextMilestone['vip_days'] += $milestone['vip_days'];
            }
        }

        $streak = [
            'model' => $streakModel,
            'currentDays' => (int) ($streakModel?->current_days ?? 0),
            'longestDays' => (int) ($streakModel?->longest_days ?? 0),
            'visitedToday' => $streakModel?->last_visit_date?->toDateString() === now(config('app.timezone'))->toDateString(),
            'milestones' => $milestones,
            'nextMilestone' => $nextMilestone,
        ];

        $periodKey = app(RecordMissionProgressAction::class)->currentPeriodKey();

        $missions = LoyaltyMission::query()
            ->active()
            ->get()
            ->map(function (LoyaltyMission $mission) use ($user, $periodKey): array {
                $progress = $mission->progressFor($user, $periodKey);

                return [
                    'mission' => $mission,
                    'progressCount' => (int) ($progress?->progress_count ?? 0),
                    'targetCount' => $mission->target_count,
                    'completedAt' => $progress?->completed_at,
                    'percent' => min(100, (int) round(((int) ($progress?->progress_count ?? 0)) / max(1, $mission->target_count) * 100)),
                ];
            })
            ->values();

        $achievements = LoyaltyAchievement::query()->active()->get();
        $earned = DB::table('loyalty_achievement_user')
            ->where('user_id', $user->getKey())
            ->get()
            ->keyBy('loyalty_achievement_id');

        return [
            'streak' => $streak,
            'missions' => $missions,
            'achievements' => [
                'all' => $achievements,
                'earnedIds' => $earned->keys(),
                'earnedMap' => $earned,
                'earnedCount' => $earned->count(),
                'totalCount' => $achievements->count(),
            ],
        ];
    }

    private function monthlySummary(?int $accountId, User $user): array
    {
        if (! $accountId) {
            return [];
        }

        $gregorian = new \DateTimeImmutable('first day of this month');
        $monthRanges = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = $gregorian->modify("-{$i} months");
            $monthRanges[] = [$start, $start->modify('+1 month')];
        }

        $rows = PointLedgerEntry::query()
            ->where('point_account_id', $accountId)
            ->where('occurred_at', '>=', $monthRanges[0][0])
            ->where('occurred_at', '<', end($monthRanges)[1])
            ->get(['direction', 'amount', 'occurred_at']);

        $summary = [];
        foreach ($monthRanges as [$start, $end]) {
            $inRange = $rows->filter(
                fn (PointLedgerEntry $entry) => $entry->occurred_at >= $start && $entry->occurred_at < $end,
            );

            $summary[] = [
                'label' => jalali_date($start, 'F'),
                'earned' => (int) $inRange->where('direction.value', 'credit')->sum('amount'),
                'spent' => (int) $inRange->where('direction.value', 'debit')->sum('amount'),
            ];
        }

        return $summary;
    }
}
