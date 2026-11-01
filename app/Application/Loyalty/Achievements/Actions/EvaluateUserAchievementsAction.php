<?php

namespace App\Application\Loyalty\Achievements\Actions;

use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Domain\Commerce\Models\Order;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\AchievementMetricEnum;
use App\Domain\Loyalty\Models\LoyaltyAchievement;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * ارزیابی نشان‌های افتخار کاربر پس از یک رویداد مرتبط.
 *
 * مقدار هر متریک با کوئری تجمیعی از وضعیت واقعی حساب می‌شود؛ نشان‌هایی که
 * آستانه‌شان رعایت شده و هنوز داده نشده‌اند، یک‌بار (unique pivot) اعطا و
 * در صورت داشتن پاداش، Point پرداخت می‌شود — بدون XP، مثل پاداش سطح.
 */
final class EvaluateUserAchievementsAction
{
    public function __construct(
        private readonly GetOrCreatePointAccountAction $getOrCreatePointAccount,
        private readonly AwardPointsAction $awardPoints,
    ) {
    }

    /**
     * @param User $user
     * @return array<int, LoyaltyAchievement> نشان‌هایی که در این فراخوانی تازه اعطا شدند
     *
     * @throws Throwable
     */
    public function execute(User $user): array
    {
        $earnedIds = DB::table('loyalty_achievement_user')
            ->where('user_id', $user->getKey())
            ->pluck('loyalty_achievement_id');

        $achievements = LoyaltyAchievement::query()
            ->active()
            ->when($earnedIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $earnedIds))
            ->get();

        if ($achievements->isEmpty()) {
            return [];
        }

        // مقدار هر متریک فقط یک‌بار per-metric محاسبه می‌شود.
        $metricCache = [];
        $newlyAwarded = [];

        foreach ($achievements as $achievement) {
            $metric = $achievement->metric;
            $metricKey = $metric->value;

            if (! array_key_exists($metricKey, $metricCache)) {
                $metricCache[$metricKey] = $this->metricValue($user, $metric);
            }

            if ($metricCache[$metricKey] < $achievement->threshold) {
                continue;
            }

            $this->award($user, $achievement);
            $newlyAwarded[] = $achievement;
        }

        return $newlyAwarded;
    }

    private function metricValue(User $user, AchievementMetricEnum $metric): int
    {
        return match ($metric) {
            AchievementMetricEnum::PAID_ORDERS_COUNT => (int) Order::query()
                ->where('user_id', $user->getKey())
                ->paid()
                ->count(),

            AchievementMetricEnum::APPROVED_COMMENTS_COUNT => (int) Comment::query()
                ->where('user_id', $user->getKey())
                ->approved()
                ->count(),

            AchievementMetricEnum::LONGEST_STREAK_DAYS => (int) \App\Domain\Loyalty\Models\LoyaltyStreak::query()
                ->where('user_id', $user->getKey())
                ->value('longest_days') ?? 0,

            AchievementMetricEnum::LIFETIME_XP => (int) $user->pointAccount()->value('lifetime_xp') ?? 0,

            AchievementMetricEnum::MEMBERSHIP_DAYS => (int) max(0, $user->created_at?->diffInDays(now())),

            AchievementMetricEnum::TOTAL_PURCHASE_AMOUNT => (int) Order::query()
                ->where('user_id', $user->getKey())
                ->paid()
                ->sum('total_amount'),
        };
    }

    /**
     * @throws Throwable
     */
    private function award(User $user, LoyaltyAchievement $achievement): void
    {
        DB::transaction(function () use ($user, $achievement): void {
            $inserted = DB::table('loyalty_achievement_user')->insertOrIgnore([
                'loyalty_achievement_id' => $achievement->getKey(),
                'user_id' => $user->getKey(),
                'awarded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! $inserted) {
                return;
            }

            if ($achievement->reward_points <= 0) {
                return;
            }

            $entry = $this->awardPoints->execute(new AwardPointsDTO(
                account: $this->getOrCreatePointAccount->execute($user),
                amount: $achievement->reward_points,
                reason: 'achievement_earned',
                idempotencyKey: "achievement:{$achievement->getKey()}:{$user->getKey()}",
                description: "پاداش نشان «{$achievement->title}»",
                metadata: [
                    'achievement_id' => $achievement->getKey(),
                    'achievement_code' => $achievement->code,
                ],
                awardsXp: false,
            ));

            DB::table('loyalty_achievement_user')
                ->where('loyalty_achievement_id', $achievement->getKey())
                ->where('user_id', $user->getKey())
                ->update(['reward_entry_id' => $entry->getKey()]);
        });
    }
}
