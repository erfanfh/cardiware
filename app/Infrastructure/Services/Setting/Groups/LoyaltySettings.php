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
    ];
}
