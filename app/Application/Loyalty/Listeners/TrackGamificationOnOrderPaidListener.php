<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Achievements\Actions\EvaluateUserAchievementsAction;
use App\Application\Loyalty\Missions\Actions\RecordMissionProgressAction;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Loyalty\Enums\MissionMetricEnum;
use Throwable;

/**
 * پیشرفت مأموریت‌ها و ارزیابی نشان‌ها پس از پرداخت موفق سفارش.
 * Loyalty هرگز نباید پرداخت کامیت‌شده را شکست دهد.
 */
class TrackGamificationOnOrderPaidListener
{
    public function __construct(
        private readonly RecordMissionProgressAction $recordMissionProgress,
        private readonly EvaluateUserAchievementsAction $evaluateAchievements,
    ) {
    }

    public function handle(OrderPaidEvent $event): void
    {
        try {
            $order = Order::query()->with('user')->find($event->orderId);

            if (! $order?->user) {
                return;
            }

            $this->recordMissionProgress->execute(
                $order->user,
                MissionMetricEnum::PAID_ORDERS,
            );

            $this->evaluateAchievements->execute($order->user);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
