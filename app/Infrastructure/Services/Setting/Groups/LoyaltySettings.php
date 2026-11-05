<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class LoyaltySettings extends BaseSettings
{
    protected string $group = 'loyalty';

    protected array $allowedKeys = [
        'point_conversion_enabled',
        'point_conversion_points_per_unit',
        'point_conversion_wallet_amount_per_unit',
        'point_conversion_minimum_points',
        'point_conversion_maximum_points_per_request',
        'point_conversion_daily_points_limit',
        'registration_gift_enabled',
        'registration_gift_reward_offer_id',
        'max_credit_percent_per_order',
        'streak_milestones',
        'points_inactivity_expiry_months',
        'vip_points_bonus_percent',
        'champion_prizes',
        'champion_year_grand_prize',
        'champion_year_min_xp',
        'champion_year_min_missions',
        'champion_year_min_orders',
        'champions_enabled_since',
        'club_raffle_campaign_id',
        'purchase_raffle_toman_per_ticket',
        'elite_min_level_id',
    ];

    protected array $casts = [
        'point_conversion_enabled' => BooleanCast::class,
        'point_conversion_points_per_unit' => IntegerCast::class,
        'point_conversion_wallet_amount_per_unit' => IntegerCast::class,
        'point_conversion_minimum_points' => IntegerCast::class,
        'point_conversion_maximum_points_per_request' => IntegerCast::class,
        'point_conversion_daily_points_limit' => IntegerCast::class,
        'registration_gift_enabled' => BooleanCast::class,
        'registration_gift_reward_offer_id' => IntegerCast::class,
        'max_credit_percent_per_order' => IntegerCast::class,
        'streak_milestones' => ArrayCast::class,
        'points_inactivity_expiry_months' => IntegerCast::class,
        'vip_points_bonus_percent' => IntegerCast::class,
        'champion_prizes' => ArrayCast::class,
        'champion_year_min_xp' => IntegerCast::class,
        'champion_year_min_missions' => IntegerCast::class,
        'champion_year_min_orders' => IntegerCast::class,
        'club_raffle_campaign_id' => IntegerCast::class,
        'purchase_raffle_toman_per_ticket' => IntegerCast::class,
        'elite_min_level_id' => IntegerCast::class,
    ];

    /**
     * جوایز پیش‌فرض نفرات ۱ تا ۳ هر دوره: امتیاز (قابل خرج، بدون XP)، کد تخفیف شخصی
     * درصدی با سقف مبلغ، و کد قرعه‌کشی در قرعه‌کشی باشگاه.
     */
    public const DEFAULT_CHAMPION_PRIZES = [
        'month' => [
            ['points' => 1000, 'coupon_percent' => 20, 'coupon_max_amount' => 200000, 'coupon_days' => 30, 'raffle_tickets' => 5],
            ['points' => 600, 'coupon_percent' => 15, 'coupon_max_amount' => 150000, 'coupon_days' => 30, 'raffle_tickets' => 3],
            ['points' => 300, 'coupon_percent' => 10, 'coupon_max_amount' => 100000, 'coupon_days' => 30, 'raffle_tickets' => 2],
        ],
        'season' => [
            ['points' => 3000, 'coupon_percent' => 30, 'coupon_max_amount' => 400000, 'coupon_days' => 60, 'raffle_tickets' => 15],
            ['points' => 2000, 'coupon_percent' => 25, 'coupon_max_amount' => 300000, 'coupon_days' => 60, 'raffle_tickets' => 10],
            ['points' => 1000, 'coupon_percent' => 20, 'coupon_max_amount' => 200000, 'coupon_days' => 60, 'raffle_tickets' => 5],
        ],
        'year' => [
            ['points' => 10000, 'coupon_percent' => 50, 'coupon_max_amount' => 1000000, 'coupon_days' => 90, 'raffle_tickets' => 50],
            ['points' => 6000, 'coupon_percent' => 40, 'coupon_max_amount' => 700000, 'coupon_days' => 90, 'raffle_tickets' => 30],
            ['points' => 3000, 'coupon_percent' => 30, 'coupon_max_amount' => 500000, 'coupon_days' => 90, 'raffle_tickets' => 15],
        ],
    ];

    /** @return list<array{points: int, coupon_percent: int, coupon_max_amount: int, coupon_days: int, raffle_tickets: int}> */
    public function championPrizes(string $periodType): array
    {
        $configured = $this->get('champion_prizes');
        $rows = is_array($configured) && is_array($configured[$periodType] ?? null)
            ? array_values($configured[$periodType])
            : self::DEFAULT_CHAMPION_PRIZES[$periodType] ?? [];

        return array_map(static fn($row): array => [
            'points' => max(0, (int) ($row['points'] ?? 0)),
            'coupon_percent' => max(0, min(100, (int) ($row['coupon_percent'] ?? 0))),
            'coupon_max_amount' => max(0, (int) ($row['coupon_max_amount'] ?? 0)),
            'coupon_days' => max(1, (int) ($row['coupon_days'] ?? 30)),
            'raffle_tickets' => max(0, (int) ($row['raffle_tickets'] ?? 0)),
        ], array_slice($rows, 0, 3));
    }
}
