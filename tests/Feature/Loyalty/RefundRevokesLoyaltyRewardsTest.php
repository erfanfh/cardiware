<?php

namespace Tests\Feature\Loyalty;

use App\Application\Commerce\Actions\Order\UpdateOrderStatusAction;
use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\LoyaltyAchievement;
use App\Domain\Loyalty\Models\LoyaltyLevel;
use App\Domain\Loyalty\Models\LoyaltyMission;
use App\Domain\Loyalty\Models\PointAccount;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * مرجوعی سفارش هر امتیازی را که بابت آن خرید داده شده برمی‌گرداند: امتیاز خرید،
 * XP سطح، پاداش سطحی که دیگر به آن نمی‌رسد، مأموریت خرید هفته و نشان خرید.
 * خرید دوباره همه را از نو می‌دهد (نه کمتر، نه دوبار).
 */
class RefundRevokesLoyaltyRewardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();

        EarningRule::query()->create([
            'code' => 'test-purchase', 'title' => 'Purchase', 'type' => EarningRuleTypeEnum::PURCHASE,
            'is_active' => true, 'priority' => 100, 'config' => ['points' => 10, 'amount_step' => 10000],
        ]);
        LoyaltyLevel::query()->create(['title' => 'تازه‌وارد', 'min_xp' => 0, 'reward_points' => 0, 'purchase_multiplier' => 1, 'color_key' => 'slate', 'sort_order' => 1, 'is_active' => true]);
        LoyaltyLevel::query()->create(['title' => 'بازیکن', 'min_xp' => 1000, 'reward_points' => 200, 'purchase_multiplier' => 1, 'color_key' => 'cyan', 'sort_order' => 2, 'is_active' => true]);
        LoyaltyMission::query()->create(['code' => 'weekly_purchase', 'title' => 'خرید هفته', 'metric' => 'paid_orders', 'target_count' => 1, 'reward_points' => 300, 'is_active' => true, 'sort_order' => 1]);
        LoyaltyAchievement::query()->create(['code' => 'first_purchase', 'title' => 'اولین خرید', 'metric' => 'paid_orders_count', 'threshold' => 1, 'reward_points' => 500, 'is_active' => true, 'sort_order' => 1]);
    }

    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        parent::tearDown();
    }

    public function test_refund_takes_back_every_purchase_reward_and_a_new_purchase_restores_them(): void
    {
        $user = User::factory()->create();
        $actions = app(UpdateOrderStatusAction::class);

        $first = $this->pendingOrder($user, 1000000);
        $actions->execute($first, OrderStatusEnum::PAID);

        // خرید ۱۰۰۰ + مأموریت ۳۰۰ (هر دو XP) + پاداش سطح ۲۰۰ + نشان ۵۰۰ (بدون XP)
        $this->assertAccount($user, balance: 2000, xp: 1300);
        $this->assertTrue($this->hasAchievement($user));

        $actions->execute($first->fresh(), OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::MANUAL_BANK_TRANSFER, refundAmount: 0);

        $this->assertAccount($user, balance: 0, xp: 0);
        $this->assertFalse($this->hasAchievement($user));
        $this->assertNull(DB::table('loyalty_mission_progress')->value('completed_at'));

        // خرید دوباره در همان هفته: همه‌ی پاداش‌ها یک‌بار دیگر داده می‌شوند.
        $second = $this->pendingOrder($user, 1000000);
        $actions->execute($second, OrderStatusEnum::PAID);

        $this->assertAccount($user, balance: 2000, xp: 1300);
        $this->assertTrue($this->hasAchievement($user));
    }

    public function test_refund_keeps_rewards_still_earned_by_other_orders(): void
    {
        $user = User::factory()->create();
        $actions = app(UpdateOrderStatusAction::class);

        $first = $this->pendingOrder($user, 1000000);
        $actions->execute($first, OrderStatusEnum::PAID);
        $second = $this->pendingOrder($user, 200000);
        $actions->execute($second, OrderStatusEnum::PAID);

        // ۱۰۰۰ + ۲۰۰ خرید، ۳۰۰ مأموریت، ۲۰۰ سطح، ۵۰۰ نشان
        $this->assertAccount($user, balance: 2200, xp: 1500);

        $actions->execute($second->fresh(), OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::MANUAL_BANK_TRANSFER);

        // فقط ۲۰۰ امتیاز خرید دوم برمی‌گردد؛ سطح، مأموریت و نشان با سفارش اول هنوز به‌دست آمده‌اند.
        $this->assertAccount($user, balance: 2000, xp: 1300);
        $this->assertTrue($this->hasAchievement($user));
    }

    private function pendingOrder(User $user, int $amount): Order
    {
        return Order::query()->create([
            'user_id' => $user->getKey(),
            'order_number' => 'LOY-' . uniqid(),
            'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => $amount,
            'discount_amount' => 0,
            'total_amount' => $amount,
        ]);
    }

    private function assertAccount(User $user, int $balance, int $xp): void
    {
        $account = PointAccount::query()->where('user_id', $user->getKey())->firstOrFail();
        $this->assertSame($balance, (int) $account->balance, 'balance');
        $this->assertSame($xp, (int) $account->lifetime_xp, 'lifetime_xp');
    }

    private function hasAchievement(User $user): bool
    {
        return DB::table('loyalty_achievement_user')->where('user_id', $user->getKey())->exists();
    }
}
