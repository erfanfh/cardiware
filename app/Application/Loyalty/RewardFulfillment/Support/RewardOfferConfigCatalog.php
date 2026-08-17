<?php

namespace App\Application\Loyalty\RewardFulfillment\Support;

use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;

class RewardOfferConfigCatalog
{
    /** @return array<string, mixed> */
    public function defaultConfig(RewardTypeEnum $type): array
    {
        return match ($type) {
            RewardTypeEnum::COUPON => [
                'coupon_type' => CouponTypeEnum::FIXED_AMOUNT->value,
                'coupon_value' => 100_000,
                'max_discount_amount' => null,
                'minimum_order_amount' => 0,
                'expires_after_days' => 7,
                'applies_to_addons' => false,
            ],
            RewardTypeEnum::RAFFLE_TICKET => [
                'ticket_quantity' => 1,
            ],
        };
    }

    /** @return array<string, list<string>> */
    public function validationRules(RewardTypeEnum $type): array
    {
        return match ($type) {
            RewardTypeEnum::COUPON => [
                'coupon_type' => ['required', 'in:fixed_amount,percentage'],
                'coupon_value' => ['required', 'integer', 'min:1'],
                'max_discount_amount' => ['nullable', 'integer', 'min:1'],
                'minimum_order_amount' => ['required', 'integer', 'min:0'],
                'expires_after_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
                'applies_to_addons' => ['required', 'boolean'],
            ],
            RewardTypeEnum::RAFFLE_TICKET => [
                'ticket_quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            ],
        };
    }
}
