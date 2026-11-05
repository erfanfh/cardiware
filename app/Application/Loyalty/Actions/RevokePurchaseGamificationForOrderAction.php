<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\DTOs\ReversePointEntryDTO;
use App\Application\Loyalty\Exceptions\PointReversalExceededException;
use App\Application\Loyalty\Missions\Actions\RecordMissionProgressAction;
use App\Application\Loyalty\Support\ReawardableLedgerKey;
use App\Domain\Commerce\Models\Order;
use App\Domain\Loyalty\Enums\AchievementMetricEnum;
use App\Domain\Loyalty\Enums\MissionMetricEnum;
use App\Domain\Loyalty\Models\LoyaltyAchievement;
use App\Domain\Loyalty\Models\LoyaltyMission;
use App\Domain\Loyalty\Models\LoyaltyMissionProgress;
use App\Domain\Loyalty\Models\PointAccount;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * بعد از لغو/مرجوعی سفارش، پاداش‌های باشگاه که فقط به خاطر همین خرید داده
 * شده بودند پس گرفته می‌شوند:
 *  - مأموریت هفتگی «خرید»: پیشرفت همان هفته یکی کم می‌شود و اگر دیگر به هدف
 *    نمی‌رسد، پاداشش برمی‌گردد و مأموریت دوباره باز می‌شود.
 *  - نشان‌های خرید (تعداد سفارش، جمع مبلغ خرید): اگر کاربر بدون این سفارش
 *    دیگر به آستانه نمی‌رسد، نشان و پاداشش برمی‌گردد و قابل کسب دوباره است.
 * امتیاز خودِ خرید را ReverseEarnedPointsForReferenceAction برمی‌گرداند.
 */
final class RevokePurchaseGamificationForOrderAction
{
    public function __construct(
        private readonly RecordMissionProgressAction $missions,
        private readonly ReversePointEntryAction $reversePointEntry,
        private readonly ReawardableLedgerKey $keys,
    ) {
    }

    /** @throws Throwable */
    public function execute(Order $order): void
    {
        $user = $order->user;
        if ($user === null) {
            return;
        }

        $account = PointAccount::query()->where('user_id', $user->getKey())->first();

        $this->revokeMissions($order, $account);

        if ($account !== null) {
            $this->revokeAchievements($order, $account);
        }
    }

    private function revokeMissions(Order $order, ?PointAccount $account): void
    {
        $paidAt = CarbonImmutable::instance($order->paid_at ?? $order->created_at ?? now())
            ->setTimezone(config('app.timezone'));
        $periodKey = $this->missions->currentPeriodKey($paidAt);

        // شمارنده‌ی مأموریت بعد از تکمیل دیگر بالا نمی‌رود، پس کم‌کردنش غلط است؛
        // تعداد سفارش‌های پرداخت‌شده‌ی همان هفته از خود سفارش‌ها شمرده می‌شود.
        $weekStart = CarbonImmutable::parse(substr($periodKey, 2), config('app.timezone'))->startOfDay();
        $paidThatWeek = (int) Order::query()
            ->where('user_id', $order->user_id)
            ->paid()
            ->whereBetween('paid_at', [$weekStart, $weekStart->addDays(7)])
            ->count();

        $missions = LoyaltyMission::query()
            ->where('metric', MissionMetricEnum::PAID_ORDERS->value)
            ->get();

        foreach ($missions as $mission) {
            DB::transaction(function () use ($mission, $order, $periodKey, $account, $paidThatWeek): void {
                $progress = LoyaltyMissionProgress::query()
                    ->where('loyalty_mission_id', $mission->getKey())
                    ->where('user_id', $order->user_id)
                    ->where('period_key', $periodKey)
                    ->lockForUpdate()
                    ->first();

                if ($progress === null) {
                    return;
                }

                $progress->progress_count = min((int) $progress->progress_count, $paidThatWeek);

                if ($progress->completed_at !== null && $paidThatWeek < (int) $mission->target_count) {
                    if ($account !== null) {
                        $this->reverseAll(
                            $account,
                            "mission:{$mission->getKey()}:{$order->user_id}:{$periodKey}",
                            "order-revoked:{$order->getKey()}:mission:{$mission->getKey()}",
                            "برگشت پاداش مأموریت «{$mission->title}» (لغو سفارش {$order->order_number})",
                            $order,
                        );
                    }
                    $progress->completed_at = null;
                    $progress->reward_entry_id = null;
                }

                $progress->save();
            });
        }
    }

    private function revokeAchievements(Order $order, PointAccount $account): void
    {
        $paidOrders = Order::query()->where('user_id', $order->user_id)->paid();
        $metrics = [
            AchievementMetricEnum::PAID_ORDERS_COUNT->value => (int) (clone $paidOrders)->count(),
            AchievementMetricEnum::TOTAL_PURCHASE_AMOUNT->value => (int) (clone $paidOrders)->sum('total_amount'),
        ];

        $earnedIds = DB::table('loyalty_achievement_user')
            ->where('user_id', $order->user_id)
            ->pluck('loyalty_achievement_id');

        $achievements = LoyaltyAchievement::query()
            ->whereIn('id', $earnedIds)
            ->whereIn('metric', array_keys($metrics))
            ->get();

        foreach ($achievements as $achievement) {
            if ($metrics[$achievement->metric->value] >= (int) $achievement->threshold) {
                continue;
            }

            DB::transaction(function () use ($achievement, $order, $account): void {
                $this->reverseAll(
                    $account,
                    "achievement:{$achievement->getKey()}:{$order->user_id}",
                    "order-revoked:{$order->getKey()}:achievement:{$achievement->getKey()}",
                    "برگشت پاداش نشان «{$achievement->title}» (لغو سفارش {$order->order_number})",
                    $order,
                );

                DB::table('loyalty_achievement_user')
                    ->where('loyalty_achievement_id', $achievement->getKey())
                    ->where('user_id', $order->user_id)
                    ->delete();
            });
        }
    }

    private function reverseAll(
        PointAccount $account,
        string $baseKey,
        string $reversalKeyPrefix,
        string $description,
        Order $order,
    ): void {
        foreach ($this->keys->activeEntries((int) $account->getKey(), $baseKey) as $row) {
            try {
                $this->reversePointEntry->execute(new ReversePointEntryDTO(
                    entry: $row['entry'],
                    amount: $row['remaining'],
                    idempotencyKey: "{$reversalKeyPrefix}:entry:{$row['entry']->getKey()}",
                    reference: $order,
                    description: $description,
                    metadata: ['order_number' => $order->order_number],
                ));
            } catch (PointReversalExceededException) {
                continue;
            }
        }
    }
}
