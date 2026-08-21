<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Actions\Reward\GrantRegistrationRewardAction;
use App\Application\Loyalty\Actions\Reward\RefundRaffleTicketRewardRedemptionAction;
use App\Application\Loyalty\Listeners\GrantRegistrationRewardListener;
use App\Domain\Identity\Events\UserRegisteredEvent;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRewardGiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_grants_configured_coupon_without_spending_points(): void
    {
        $offer = $this->createCouponOffer('welcome-10', 10);
        $this->configureGift(true, $offer);
        $user = User::factory()->create();
        $registeredAt = CarbonImmutable::parse('2026-08-21 10:00:00');

        $redemption = app(GrantRegistrationRewardAction::class)
            ->execute($user, $registeredAt);

        $this->assertNotNull($redemption);
        $this->assertSame(0, $redemption->points_spent);
        $this->assertNull($redemption->point_ledger_entry_id);
        $this->assertSame(RewardRedemptionStatusEnum::COMPLETED, $redemption->status);
        $this->assertTrue($redemption->isRegistrationGift());
        $this->assertSame('registration_gift', data_get($redemption->metadata, 'source'));
        $this->assertDatabaseCount('point_ledger_entries', 0);
        $this->assertDatabaseCount('reward_redemptions', 1);
        $this->assertDatabaseHas('coupon_users', [
            'coupon_id' => $redemption->coupon_id,
            'user_id' => $user->getKey(),
        ]);

        $coupon = $redemption->coupon;
        $this->assertNotNull($coupon);
        $this->assertSame(10, $coupon->value);
        $this->assertSame('loyalty_registration_gift', data_get($coupon->metadata, 'source'));
        $this->assertSame(1, $coupon->usage_limit);
        $this->assertSame(1, $coupon->usage_limit_per_user);
        $this->assertSame(7.0, $coupon->starts_at->diffInDays($coupon->expires_at));
        $this->assertSame(1, $offer->refresh()->redeemed_count);
    }

    public function test_registration_gift_is_idempotent_even_if_admin_changes_the_selected_offer(): void
    {
        $firstOffer = $this->createCouponOffer('welcome-first', 10);
        $secondOffer = $this->createCouponOffer('welcome-second', 20);
        $this->configureGift(true, $firstOffer);
        $user = User::factory()->create();
        $action = app(GrantRegistrationRewardAction::class);
        $registeredAt = now()->toImmutable();

        $first = $action->execute($user, $registeredAt);
        $this->configureGift(true, $secondOffer);
        $replayed = $action->execute($user, $registeredAt);

        $this->assertSame($first?->getKey(), $replayed?->getKey());
        $this->assertSame($firstOffer->getKey(), $replayed?->reward_offer_id);
        $this->assertDatabaseCount('reward_redemptions', 1);
        $this->assertDatabaseCount('coupons', 1);
        $this->assertSame(1, $firstOffer->refresh()->redeemed_count);
        $this->assertSame(0, $secondOffer->refresh()->redeemed_count);
    }

    public function test_user_registered_event_is_wired_to_the_gift_grant(): void
    {
        $offer = $this->createCouponOffer('event-welcome', 15);
        $this->configureGift(true, $offer);
        $user = User::factory()->create();

        event(new UserRegisteredEvent(
            userId: (int) $user->getKey(),
            occurredAt: now()->toImmutable(),
        ));

        $this->assertDatabaseHas('reward_redemptions', [
            'user_id' => $user->getKey(),
            'reward_offer_id' => $offer->getKey(),
            'points_spent' => 0,
            'status' => RewardRedemptionStatusEnum::COMPLETED->value,
        ]);
        $this->assertDatabaseCount('coupons', 1);
        $this->assertDatabaseCount('point_ledger_entries', 0);
    }

    public function test_disabled_gift_does_not_create_any_fulfillment(): void
    {
        $offer = $this->createCouponOffer('disabled-welcome', 10);
        $this->configureGift(false, $offer);

        $redemption = app(GrantRegistrationRewardAction::class)->execute(
            User::factory()->create(),
            now()->toImmutable(),
        );

        $this->assertNull($redemption);
        $this->assertDatabaseCount('reward_redemptions', 0);
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_an_unavailable_gift_never_breaks_the_registration_listener(): void
    {
        $offer = $this->createCouponOffer('inactive-welcome', 10);
        $this->configureGift(true, $offer);
        $offer->update(['is_active' => false]);
        $user = User::factory()->create();

        app(GrantRegistrationRewardListener::class)->handle(new UserRegisteredEvent(
            userId: (int) $user->getKey(),
            occurredAt: now()->toImmutable(),
        ));

        $this->assertDatabaseHas('users', ['id' => $user->getKey()]);
        $this->assertDatabaseCount('reward_redemptions', 0);
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_a_free_raffle_gift_can_be_reversed_without_a_fake_point_refund(): void
    {
        $user = User::factory()->create();
        $offer = RewardOffer::query()->create([
            'code' => 'free-registration-ticket',
            'title' => 'Free registration ticket',
            'type' => RewardTypeEnum::RAFFLE_TICKET,
            'points_cost' => 500,
            'is_active' => true,
            'sort_order' => 0,
            'config' => ['ticket_quantity' => 1],
        ]);
        $redemption = RewardRedemption::query()->create([
            'reward_offer_id' => $offer->getKey(),
            'user_id' => $user->getKey(),
            'point_ledger_entry_id' => null,
            'points_spent' => 0,
            'status' => RewardRedemptionStatusEnum::COMPLETED,
            'idempotency_key' => 'test:free-registration-raffle-refund',
            'fulfilled_at' => now(),
            'metadata' => ['source' => 'registration_gift'],
        ]);

        $reversed = app(RefundRaffleTicketRewardRedemptionAction::class)->execute(
            $redemption,
            'Campaign cancelled',
        );

        $this->assertSame(RewardRedemptionStatusEnum::REVERSED, $reversed->status);
        $this->assertNull($reversed->reversal_point_ledger_entry_id);
        $this->assertDatabaseCount('point_ledger_entries', 0);
    }

    private function configureGift(bool $enabled, RewardOffer $offer): void
    {
        $settings = app(LoyaltySettings::class);
        $settings->set('registration_gift_enabled', $enabled);
        $settings->set('registration_gift_reward_offer_id', $offer->getKey());
    }

    private function createCouponOffer(string $code, int $percentage): RewardOffer
    {
        return RewardOffer::query()->create([
            'code' => $code,
            'title' => "Welcome {$percentage}% coupon",
            'type' => RewardTypeEnum::COUPON,
            'points_cost' => 500,
            'is_active' => true,
            'sort_order' => 0,
            'redemption_limit_per_user' => 1,
            'config' => [
                'coupon_type' => 'percentage',
                'coupon_value' => $percentage,
                'max_discount_amount' => 200_000,
                'minimum_order_amount' => 0,
                'expires_after_days' => 7,
                'applies_to_addons' => false,
            ],
        ]);
    }
}
