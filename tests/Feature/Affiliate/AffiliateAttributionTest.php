<?php

namespace Tests\Feature\Affiliate;

use App\Application\Affiliate\Actions\RequestAffiliateWithdrawalAction;
use App\Application\Affiliate\Actions\ResolveAffiliateWithdrawalAction;
use App\Domain\Affiliate\Enums\AffiliateOrderStatusEnum;
use App\Domain\Affiliate\Models\Affiliate;
use App\Domain\Affiliate\Models\AffiliateOrder;
use App\Domain\Affiliate\Models\AffiliateReferral;
use App\Domain\Affiliate\Models\AffiliateWithdrawal;
use App\Domain\Affiliate\Models\AffiliateClick;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Events\UserRegisteredEvent;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Promotion\Models\CouponRedemption;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateAttributionTest extends TestCase
{
    use RefreshDatabase;

    private User $affiliateOwner;

    private Affiliate $affiliate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\Seeders\Identity\PermissionSeeder']);

        $this->affiliateOwner = User::factory()->create();
        PointAccount::query()->create(['user_id' => $this->affiliateOwner->getKey()]);

        $this->affiliate = Affiliate::query()->create([
            'user_id' => $this->affiliateOwner->getKey(),
            'code' => 'TESTAFF',
            'is_active' => true,
            'commission_type' => 'percent',
            'commission_value' => 25,
            'cookie_days' => 30,
            'attribution_days' => 90,
            'min_withdrawal_amount' => 100000,
        ]);
    }

    public function test_coupon_redemption_with_affiliate_code_records_commission(): void
    {
        $buyer = User::factory()->create();
        $order = $this->paidOrder($buyer, 400000);

        CouponRedemption::query()->create([
            'coupon_id' => null,
            'user_id' => $buyer->getKey(),
            'order_id' => $order->getKey(),
            'code' => 'testaff',
            'order_subtotal' => 400000,
            'eligible_amount' => 400000,
            'discount_amount' => 0,
            'redeemed_at' => now(),
        ]);

        event(new OrderPaidEvent($order->getKey(), CarbonImmutable::now()));

        $this->assertDatabaseHas('affiliate_orders', [
            'affiliate_id' => $this->affiliate->getKey(),
            'order_id' => $order->getKey(),
            'order_amount' => 400000,
            'commission_amount' => 100000,
            'status' => AffiliateOrderStatusEnum::ACTIVE->value,
            'source' => 'coupon',
        ]);
    }

    public function test_referral_first_purchase_pays_commission_and_referral_bonuses_once(): void
    {
        $referred = User::factory()->create();
        PointAccount::query()->create(['user_id' => $referred->getKey()]);

        AffiliateReferral::query()->create([
            'affiliate_id' => $this->affiliate->getKey(),
            'referred_user_id' => $referred->getKey(),
            'created_at' => now()->subDays(10),
        ]);

        $firstOrder = $this->paidOrder($referred, 500000);
        event(new OrderPaidEvent($firstOrder->getKey(), CarbonImmutable::now()));

        $this->assertDatabaseHas('affiliate_orders', [
            'order_id' => $firstOrder->getKey(),
            'commission_amount' => 125000,
            'source' => 'referral',
        ]);

        // پاداش معرف و معرف‌شده هر دو یک بار پرداخت شد
        $this->assertSame(200, (int) PointLedgerEntry::query()
            ->where('point_account_id', $this->affiliateOwner->pointAccount->getKey())
            ->where('reason', 'referral_bonus')
            ->sum('amount'));
        $this->assertSame(500, (int) PointLedgerEntry::query()
            ->where('point_account_id', $referred->pointAccount->getKey())
            ->where('reason', 'referral_bonus')
            ->sum('amount'));

        // خرید دوم: کمیسیون جدید ولی بدون پاداش معرفی مجدد
        $secondOrder = $this->paidOrder($referred, 200000);
        event(new OrderPaidEvent($secondOrder->getKey(), CarbonImmutable::now()));

        $this->assertSame(2, AffiliateOrder::query()
            ->where('affiliate_id', $this->affiliate->getKey())
            ->count());
        $this->assertSame(200, (int) PointLedgerEntry::query()
            ->where('reason', 'referral_bonus')
            ->where('point_account_id', $this->affiliateOwner->pointAccount->getKey())
            ->sum('amount'));
    }

    public function test_every_referred_sale_earns_points_and_a_refund_takes_them_back(): void
    {
        $buyer = User::factory()->create();
        $order = $this->paidOrder($buyer, 400000);
        CouponRedemption::query()->create([
            'coupon_id' => null,
            'user_id' => $buyer->getKey(),
            'order_id' => $order->getKey(),
            'code' => 'TESTAFF',
            'order_subtotal' => 400000,
            'eligible_amount' => 400000,
            'discount_amount' => 0,
            'redeemed_at' => now(),
        ]);

        event(new OrderPaidEvent($order->getKey(), CarbonImmutable::now()));

        // پیش‌فرض ۲ امتیاز به ازای هر ۱۰۰۰ تومان فروش
        $account = $this->affiliateOwner->pointAccount()->first();
        $this->assertSame(800, (int) $account->balance);
        $this->assertSame(800, (int) $account->lifetime_xp);

        event(new OrderPaymentRevokedEvent($order->getKey(), 'refund', CarbonImmutable::now()));

        $account->refresh();
        $this->assertSame(0, (int) $account->balance);
        $this->assertSame(0, (int) $account->lifetime_xp);
    }

    public function test_self_purchase_with_own_code_is_not_attributed(): void
    {
        $order = $this->paidOrder($this->affiliateOwner, 400000);

        CouponRedemption::query()->create([
            'coupon_id' => null,
            'user_id' => $this->affiliateOwner->getKey(),
            'order_id' => $order->getKey(),
            'code' => 'TESTAFF',
            'order_subtotal' => 400000,
            'eligible_amount' => 400000,
            'discount_amount' => 0,
            'redeemed_at' => now(),
        ]);

        event(new OrderPaidEvent($order->getKey(), CarbonImmutable::now()));

        $this->assertDatabaseMissing('affiliate_orders', ['order_id' => $order->getKey()]);
    }

    public function test_duplicate_paid_event_is_idempotent(): void
    {
        $buyer = User::factory()->create();
        $order = $this->paidOrder($buyer, 300000);

        CouponRedemption::query()->create([
            'coupon_id' => null,
            'user_id' => $buyer->getKey(),
            'order_id' => $order->getKey(),
            'code' => 'TESTAFF',
            'order_subtotal' => 300000,
            'eligible_amount' => 300000,
            'discount_amount' => 0,
            'redeemed_at' => now(),
        ]);

        event(new OrderPaidEvent($order->getKey(), CarbonImmutable::now()));
        event(new OrderPaidEvent($order->getKey(), CarbonImmutable::now()));

        $this->assertSame(1, AffiliateOrder::query()
            ->where('order_id', $order->getKey())
            ->count());
    }

    public function test_refund_reverts_commission_and_reduces_available_balance(): void
    {
        $buyer = User::factory()->create();
        $order = $this->paidOrder($buyer, 400000);

        CouponRedemption::query()->create([
            'coupon_id' => null,
            'user_id' => $buyer->getKey(),
            'order_id' => $order->getKey(),
            'code' => 'TESTAFF',
            'order_subtotal' => 400000,
            'eligible_amount' => 400000,
            'discount_amount' => 0,
            'redeemed_at' => now(),
        ]);

        event(new OrderPaidEvent($order->getKey(), CarbonImmutable::now()));
        event(new OrderPaymentRevokedEvent($order->getKey(), 'refund', CarbonImmutable::now()));

        $this->assertDatabaseHas('affiliate_orders', [
            'order_id' => $order->getKey(),
            'status' => AffiliateOrderStatusEnum::REVERTED->value,
        ]);

        $overview = app(\App\Application\Affiliate\Actions\GetAffiliateOverviewAction::class)->execute($this->affiliate);
        $this->assertSame(0, $overview['availableBalance']);
        $this->assertSame(100000, $overview['revertedCommission']);
    }

    public function test_referral_binding_blocks_self_registration_of_affiliate_owner(): void
    {
        // ایمیل و موبایل در دیتابیس یکتا هستند؛ سناریوی واقعی ضدتقلب،
        // خودِ صاحب کد است که نباید به خودش وصل شود.
        request()->cookies->set('mw_ref', (string) $this->affiliate->getKey());

        event(new UserRegisteredEvent(
            $this->affiliateOwner->getKey(),
            CarbonImmutable::now(),
        ));

        $this->assertDatabaseMissing('affiliate_referrals', [
            'referred_user_id' => $this->affiliateOwner->getKey(),
        ]);
    }

    public function test_referral_binding_with_cookie_links_user(): void
    {
        $newUser = User::factory()->create(['mobile' => '09120000099']);

        request()->cookies->set('mw_ref', (string) $this->affiliate->getKey());

        event(new UserRegisteredEvent(
            $newUser->getKey(),
            CarbonImmutable::now(),
        ));

        $this->assertDatabaseHas('affiliate_referrals', [
            'affiliate_id' => $this->affiliate->getKey(),
            'referred_user_id' => $newUser->getKey(),
        ]);
    }

    public function test_ref_link_records_click_and_sets_cookie(): void
    {
        // مسیری سبک داخل همان گروه middleware (صفحه اصلی در محیط تست layout ندارد)
        $response = $this->get('/loyalty/leaderboard?ref=TESTAFF');

        $response->assertOk()->assertCookie('mw_ref');

        $this->assertSame(1, AffiliateClick::query()
            ->where('affiliate_id', $this->affiliate->getKey())
            ->count());
    }

    public function test_withdrawal_flow_from_request_to_approval(): void
    {
        $buyer = User::factory()->create();
        $order = $this->paidOrder($buyer, 1000000);

        CouponRedemption::query()->create([
            'coupon_id' => null,
            'user_id' => $buyer->getKey(),
            'order_id' => $order->getKey(),
            'code' => 'TESTAFF',
            'order_subtotal' => 1000000,
            'eligible_amount' => 1000000,
            'discount_amount' => 0,
            'redeemed_at' => now(),
        ]);

        event(new OrderPaidEvent($order->getKey(), CarbonImmutable::now()));

        // زیر حداقل برداشت رد می‌شود
        try {
            app(RequestAffiliateWithdrawalAction::class)->execute($this->affiliate, 50000, null, null);
            $this->fail('Expected DomainException for below-minimum withdrawal.');
        } catch (DomainException) {
            $this->addToAssertionCount(1);
        }

        $withdrawal = app(RequestAffiliateWithdrawalAction::class)
            ->execute($this->affiliate, 200000, '589210-12', null);

        $this->assertSame('pending', $withdrawal->status->value);

        // درخواست موازی وقتی یکی در انتظار است رد می‌شود
        try {
            app(RequestAffiliateWithdrawalAction::class)->execute($this->affiliate, 50000, null, null);
            $this->fail('Expected DomainException for concurrent withdrawal.');
        } catch (DomainException) {
            $this->addToAssertionCount(1);
        }

        app(ResolveAffiliateWithdrawalAction::class)->execute(
            $withdrawal,
            User::factory()->create(),
            true,
            'واریز شد',
        );

        $overview = app(\App\Application\Affiliate\Actions\GetAffiliateOverviewAction::class)->execute($this->affiliate);
        $this->assertSame(250000, $overview['totalCommission']);
        $this->assertSame(200000, $overview['paidOut']);
        $this->assertSame(50000, $overview['availableBalance']);
    }

    public function test_rejected_withdrawal_returns_balance(): void
    {
        $buyer = User::factory()->create();
        $order = $this->paidOrder($buyer, 800000);

        CouponRedemption::query()->create([
            'coupon_id' => null,
            'user_id' => $buyer->getKey(),
            'order_id' => $order->getKey(),
            'code' => 'TESTAFF',
            'order_subtotal' => 800000,
            'eligible_amount' => 800000,
            'discount_amount' => 0,
            'redeemed_at' => now(),
        ]);

        event(new OrderPaidEvent($order->getKey(), CarbonImmutable::now()));

        $withdrawal = app(RequestAffiliateWithdrawalAction::class)
            ->execute($this->affiliate, 200000, null, null);

        app(ResolveAffiliateWithdrawalAction::class)
            ->execute($withdrawal, User::factory()->create(), false, 'مستندات ناقص');

        $overview = app(\App\Application\Affiliate\Actions\GetAffiliateOverviewAction::class)->execute($this->affiliate);
        $this->assertSame(0, $overview['paidOut']);
        $this->assertSame(200000, $overview['availableBalance']);
    }

    private function paidOrder(User $buyer, int $amount): Order
    {
        return Order::query()->create([
            'user_id' => $buyer->getKey(),
            'order_number' => 'AFF-' . uniqid(),
            'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => $amount,
            'total_amount' => $amount,
            'paid_at' => now(),
            'metadata' => [],
        ]);
    }
}
