<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Actions\Reward\GrantRegistrationRewardAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAudienceClubCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_only_renders_the_coupon_section_when_a_usable_loyalty_coupon_exists(): void
    {
        $user = User::factory()->create();
        $pointAccount = new PointAccount;
        $pointAccount->forceFill([
            'user_id' => $user->getKey(),
            'balance' => 1_250,
            'lifetime_earned' => 1_250,
        ])->save();

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSee('باشگاه مخاطبان')
            ->assertSee('1,250')
            ->assertDontSee('هدیه‌های آماده استفاده');

        $offer = $this->createCouponOffer();
        $settings = app(LoyaltySettings::class);
        $settings->set('registration_gift_enabled', true);
        $settings->set('registration_gift_reward_offer_id', $offer->getKey());

        $redemption = app(GrantRegistrationRewardAction::class)->execute(
            $user,
            now()->toImmutable(),
        );
        $coupon = $redemption?->coupon;

        $this->assertNotNull($coupon);

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSee('هدیه‌های آماده استفاده')
            ->assertSee($coupon->code)
            ->assertSee('15٪ تخفیف');

        $coupon->forceFill(['used_count' => 1])->save();

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertDontSee('هدیه‌های آماده استفاده')
            ->assertDontSee($coupon->code);
    }

    private function createCouponOffer(): RewardOffer
    {
        return RewardOffer::query()->create([
            'code' => 'dashboard-welcome-coupon',
            'title' => 'کد تخفیف خوش‌آمدگویی',
            'type' => RewardTypeEnum::COUPON,
            'points_cost' => 500,
            'is_active' => true,
            'sort_order' => 0,
            'redemption_limit_per_user' => 1,
            'config' => [
                'coupon_type' => 'percentage',
                'coupon_value' => 15,
                'max_discount_amount' => 200_000,
                'minimum_order_amount' => 0,
                'expires_after_days' => 7,
                'applies_to_addons' => false,
            ],
        ]);
    }
}
