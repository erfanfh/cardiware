<?php

namespace App\Application\Loyalty\Notifications;

use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\LoyaltyTimeSpentSession;
use App\Domain\Loyalty\Models\PointLedgerEntry;

class LoyaltyEarnedNotificationFactory
{
    /**
     * @return array<string, int|string>
     */
    public function forDailyVisit(PointLedgerEntry $entry): array
    {
        return $this->make(
            entry: $entry,
            type: EarningRuleTypeEnum::DAILY_VISIT,
            title: 'امروز را با امتیاز شروع کردی!',
            actionLabel: 'اولین ورود امروز',
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function forTimeSpent(
        PointLedgerEntry $entry,
        LoyaltyTimeSpentSession $session,
    ): array {
        $requiredSeconds = max(
            1,
            (int) data_get($session->earningRule?->config, 'required_seconds', 1),
        );

        return $this->make(
            entry: $entry,
            type: EarningRuleTypeEnum::TIME_SPENT,
            title: 'وقتت به امتیاز تبدیل شد!',
            actionLabel: $session->target_type === 'product'
                ? 'مشاهده این محصول'
                : 'مطالعه این نوشته',
            context: [
                'required_seconds' => $requiredSeconds,
                'target_type' => $session->target_type,
            ],
        );
    }

    /**
     * @param  array<string, int|string>  $context
     * @return array<string, int|string>
     */
    private function make(
        PointLedgerEntry $entry,
        EarningRuleTypeEnum $type,
        string $title,
        string $actionLabel,
        array $context = [],
    ): array {
        return array_merge([
            'id' => (string) $entry->public_token,
            'type' => $type->value,
            'points' => (int) $entry->amount,
            'balance_after' => (int) $entry->balance_after,
            'title' => $title,
            'action_label' => $actionLabel,
        ], $context);
    }
}
