<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Actions\RewardOffer\UpdateRewardOfferAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardOfferHistoricalLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_reward_type_stays_locked_even_when_current_redeemed_count_returns_to_zero(): void
    {
        $user = User::factory()->create();
        $account = PointAccount::query()->create(['user_id' => $user->getKey()]);
        $entry = PointLedgerEntry::query()->create([
            'point_account_id' => $account->getKey(),
            'type' => PointEntryTypeEnum::SPEND,
            'direction' => PointEntryDirectionEnum::DEBIT,
            'reason' => 'reward_redemption',
            'amount' => 500,
            'balance_before' => 500,
            'balance_after' => 0,
            'idempotency_key' => 'test:history-lock:spend',
            'idempotency_fingerprint' => str_repeat('b', 64),
            'metadata' => [],
            'occurred_at' => now(),
        ]);

        $offer = RewardOffer::query()->create([
            'code' => 'history-lock-reward',
            'title' => 'History lock',
            'type' => RewardTypeEnum::COUPON,
            'points_cost' => 500,
            'is_active' => true,
            'sort_order' => 0,
            'redeemed_count' => 0,
            'config' => [
                'coupon_type' => 'fixed_amount',
                'coupon_value' => 100000,
                'minimum_order_amount' => 0,
                'expires_after_days' => 7,
                'applies_to_addons' => false,
            ],
        ]);

        RewardRedemption::query()->create([
            'reward_offer_id' => $offer->getKey(),
            'user_id' => $user->getKey(),
            'point_ledger_entry_id' => $entry->getKey(),
            'points_spent' => 500,
            'status' => RewardRedemptionStatusEnum::REVERSED,
            'idempotency_key' => 'test:history-lock:redemption',
            'reversed_at' => now(),
            'metadata' => [],
        ]);

        $this->expectException(DomainException::class);

        app(UpdateRewardOfferAction::class)->execute($offer, [
            'code' => $offer->code,
            'title' => $offer->title,
            'description' => null,
            'type' => RewardTypeEnum::RAFFLE_TICKET,
            'points_cost' => 500,
            'is_active' => true,
            'sort_order' => 0,
            'starts_at' => null,
            'ends_at' => null,
            'redemption_limit' => null,
            'redemption_limit_per_user' => 1,
            'target_id' => null,
            'config' => ['ticket_quantity' => 1],
        ]);
    }
}
