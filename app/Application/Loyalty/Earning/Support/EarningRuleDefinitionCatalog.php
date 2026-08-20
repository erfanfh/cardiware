<?php

namespace App\Application\Loyalty\Earning\Support;

use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;

class EarningRuleDefinitionCatalog
{
    /**
     * @return array<string, mixed>
     */
    public function defaultConfig(EarningRuleTypeEnum $type): array
    {
        return match ($type) {
            EarningRuleTypeEnum::REGISTRATION => [
                'points' => 100,
            ],
            EarningRuleTypeEnum::DAILY_VISIT => [
                'points' => 5,
            ],
            EarningRuleTypeEnum::PURCHASE => [
                'amount_step' => 100_000,
                'points' => 10,
                'minimum_order_amount' => 0,
                'max_points_per_order' => null,
            ],
            EarningRuleTypeEnum::COMMENT => [
                'points' => 10,
                'daily_limit' => 3,
                'include_replies' => false,
            ],
            EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY => [
                'points' => 100,
                'minimum_membership_years' => 1,
            ],
            EarningRuleTypeEnum::TIME_SPENT => [
                'points' => 5,
                'required_seconds' => 60,
                'daily_limit' => 5,
            ],
        };
    }

    /**
     * Validation rules are intentionally centralized here so the future admin CRUD
     * and API/import paths validate exactly the same config shape used by handlers.
     *
     * @return array<string, list<string>>
     */
    public function validationRules(EarningRuleTypeEnum $type): array
    {
        return match ($type) {
            EarningRuleTypeEnum::REGISTRATION,
            EarningRuleTypeEnum::DAILY_VISIT => [
                'points' => ['required', 'integer', 'min:1'],
            ],
            EarningRuleTypeEnum::PURCHASE => [
                'amount_step' => ['required', 'integer', 'min:1'],
                'points' => ['required', 'integer', 'min:1'],
                'minimum_order_amount' => ['required', 'integer', 'min:0'],
                'max_points_per_order' => ['nullable', 'integer', 'min:1'],
            ],
            EarningRuleTypeEnum::COMMENT => [
                'points' => ['required', 'integer', 'min:1'],
                'daily_limit' => ['required', 'integer', 'min:1'],
                'include_replies' => ['required', 'boolean'],
            ],
            EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY => [
                'points' => ['required', 'integer', 'min:1'],
                'minimum_membership_years' => ['required', 'integer', 'min:1'],
            ],
            EarningRuleTypeEnum::TIME_SPENT => [
                'points' => ['required', 'integer', 'min:1'],
                'required_seconds' => ['required', 'integer', 'min:10'],
                'daily_limit' => ['required', 'integer', 'min:1'],
            ],
        };
    }
}
