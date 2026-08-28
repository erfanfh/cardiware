<?php

namespace Tests\Feature\Loyalty;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardRedemptionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_redemption_generates_public_token_and_supports_updated_at_lifecycle(): void
    {
        $user = User::factory()->create();
        $offer = $this->createCouponOffer();
        $entry = $this->createSpendEntry($user);

        $redemption = RewardRedemption::query()->create([
            'reward_offer_id' => $offer->getKey(),
            'user_id' => $user->getKey(),
            'point_ledger_entry_id' => $entry->getKey(),
            'points_spent' => 500,
            'status' => RewardRedemptionStatusEnum::PENDING,
            'idempotency_key' => 'test:redemption:1',
            'metadata' => [],
        ]);

        $this->assertNotEmpty($redemption->public_token);
        $this->assertNotNull($redemption->created_at);
        $this->assertNotNull($redemption->updated_at);

        $redemption->forceFill([
            'status' => RewardRedemptionStatusEnum::COMPLETED,
            'fulfilled_at' => now(),
        ])->save();

        $this->assertSame(
            RewardRedemptionStatusEnum::COMPLETED,
            $redemption->refresh()->status,
        );
    }

    public function test_history_title_prefers_redemption_snapshot_over_current_offer_title(): void
    {
        $user = User::factory()->create();
        $offer = $this->createCouponOffer();
        $entry = $this->createSpendEntry($user);

        $redemption = RewardRedemption::query()->create([
            'reward_offer_id' => $offer->getKey(),
            'user_id' => $user->getKey(),
            'point_ledger_entry_id' => $entry->getKey(),
            'points_spent' => 500,
            'status' => RewardRedemptionStatusEnum::COMPLETED,
            'idempotency_key' => 'test:redemption:snapshot-title',
            'metadata' => [
                'reward_snapshot' => [
                    'title' => 'Original reward title',
                ],
            ],
        ]);

        $offer->update(['title' => 'Changed later']);

        $this->assertSame('Original reward title', $redemption->refresh()->rewardTitle());
    }

    private function createCouponOffer(): RewardOffer
    {
        return RewardOffer::query()->create([
            'code' => 'test-coupon-reward',
            'title' => 'Test coupon reward',
            'type' => RewardTypeEnum::COUPON,
            'points_cost' => 500,
            'is_active' => true,
            'sort_order' => 0,
            'config' => [
                'coupon_type' => 'fixed_amount',
                'coupon_value' => 100000,
                'minimum_order_amount' => 0,
                'expires_after_days' => 7,
                'applies_to_addons' => false,
            ],
        ]);
    }

    private function createSpendEntry(User $user): PointLedgerEntry
    {
        $account = PointAccount::query()->create(['user_id' => $user->getKey()]);

        return PointLedgerEntry::query()->create([
            'point_account_id' => $account->getKey(),
            'type' => PointEntryTypeEnum::SPEND,
            'direction' => PointEntryDirectionEnum::DEBIT,
            'reason' => 'reward_redemption',
            'amount' => 500,
            'balance_before' => 500,
            'balance_after' => 0,
            'idempotency_key' => 'test:spend:1',
            'idempotency_fingerprint' => str_repeat('a', 64),
            'metadata' => [],
            'occurred_at' => now(),
        ]);
    }
}
