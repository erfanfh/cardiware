<?php

namespace Tests\Feature\Vip;

use App\Application\Commerce\Actions\Cart\AddPurchasableToCartAction;
use App\Application\Commerce\Actions\Checkout\CompleteFreeOrderAction;
use App\Application\Commerce\Actions\Checkout\CompleteOrderAfterSuccessfulPaymentAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Application\Promotion\Actions\Coupon\ApplyCouponToCartAction;
use App\Application\Promotion\Actions\Coupon\RemoveCouponFromCartAction;
use App\Application\Promotion\Exceptions\CouponMinimumOrderAmountNotMetException;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Vip\Models\VipPlan;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Livewire\Front\Commerce\Cart\CartPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class VipCouponCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function plan(): VipPlan
    {
        return VipPlan::query()->create([
            'code' => 'vip-month',
            'title' => 'پلن ماهانه',
            'duration_days' => 30,
            'price_toman' => 100000,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function coupon(string $code, CouponScopeEnum $scope, int $value = 25, int $minimum = 0): Coupon
    {
        return Coupon::query()->create([
            'code' => $code,
            'title' => $code,
            'type' => CouponTypeEnum::PERCENTAGE,
            'value' => $value,
            'scope' => $scope,
            'status' => CouponStatusEnum::ACTIVE,
            'minimum_order_amount' => $minimum,
            'applies_to_all_users' => true,
            'applies_to_addons' => false,
        ]);
    }

    private function addProduct(): void
    {
        $now = now();
        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Test Store', 'slug' => 'test-store', 'status' => 'active',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId, 'title' => 'Test Product', 'slug' => 'test-product',
            'type' => 'digital', 'structure' => 'simple', 'status' => 'active',
            'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $saleItemId = DB::table('product_sale_items')->insertGetId([
            'product_id' => $productId, 'sku' => 'VIP-COUPON-MIXED',
            'base_price' => 50000, 'status' => 'available',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        app(AddPurchasableToCartAction::class)->execute(ProductSaleItem::query()->findOrFail($saleItemId));
    }

    public function test_admin_can_create_vip_coupon_and_paid_checkout_redeems_it(): void
    {
        $adminRole = Role::factory()->create(['is_super_admin' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $adminRole->id]));

        $this->get(route('admin.coupons.create'))->assertOk()->assertSee('اشتراک‌های VIP');
        $this->post(route('admin.coupons.store'), [
            'status' => 'active',
            'title' => 'تخفیف اشتراک ویژه',
            'code' => 'VIP25',
            'type' => 'percentage',
            'value' => 25,
            'minimum_order_amount' => 50000,
            'is_floating_box_enabled' => 0,
            'applies_to_all_users' => 1,
            'scope' => 'vip',
            'applies_to_addons' => 0,
        ])->assertRedirect();

        $coupon = Coupon::query()->where('code', 'VIP25')->firstOrFail();
        $this->assertSame(CouponScopeEnum::VIP, $coupon->scope);

        $buyer = User::factory()->create();
        $plan = $this->plan();
        $this->actingAs($buyer)->post(route('vip.cart.add', $plan));
        $cart = Cart::query()->where('user_id', $buyer->id)->active()->firstOrFail();
        Livewire::test(CartPage::class)
            ->set('couponCode', 'VIP25')
            ->call('applyCoupon')
            ->assertSet('checkoutNotice', 'کدتخفیف با موفقیت اعمال شد.');

        $cart->refresh();
        $this->assertSame(100000, (int) $cart->coupon_eligible_amount);
        $this->assertSame(25000, (int) $cart->coupon_discount_amount);
        $this->assertSame(75000, (int) $cart->total_amount);

        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(25000, (int) $order->items()->firstOrFail()->coupon_discount_amount);
        $this->assertDatabaseHas('coupon_reservations', ['order_id' => $order->id, 'coupon_id' => $coupon->id]);

        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($order);
        $this->assertDatabaseHas('coupon_redemptions', ['order_id' => $order->id, 'coupon_id' => $coupon->id]);
        $this->assertDatabaseHas('vip_subscriptions', ['order_id' => $order->id, 'user_id' => $buyer->id]);
    }

    public function test_vip_coupon_only_discounts_vip_and_old_order_coupon_only_discounts_products(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer);
        $plan = $this->plan();
        $this->addProduct();
        $this->post(route('vip.cart.add', $plan));

        $cart = Cart::query()->where('user_id', $buyer->id)->active()->firstOrFail();
        $this->coupon('VIP-MIX', CouponScopeEnum::VIP);
        $this->coupon('PRODUCT-MIX', CouponScopeEnum::ORDER);

        app(ApplyCouponToCartAction::class)->execute($cart, 'VIP-MIX', $buyer);
        $items = $cart->fresh()->items()->get()->keyBy('purchasable_type');
        $this->assertSame(25000, (int) $items['vip_plan']->coupon_discount_amount);
        $this->assertSame(0, (int) $items['product_sale_item']->coupon_discount_amount);

        app(ApplyCouponToCartAction::class)->execute($cart, 'PRODUCT-MIX', $buyer);
        $items = $cart->fresh()->items()->get()->keyBy('purchasable_type');
        $this->assertSame(0, (int) $items['vip_plan']->coupon_discount_amount);
        $this->assertSame(12500, (int) $items['product_sale_item']->coupon_discount_amount);
    }

    public function test_full_vip_discount_completes_free_checkout_and_grants_subscription(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer);
        $plan = $this->plan();
        $this->post(route('vip.cart.add', $plan));
        $cart = Cart::query()->where('user_id', $buyer->id)->active()->firstOrFail();

        $coupon = $this->coupon('VIP-FREE', CouponScopeEnum::VIP, 100);
        app(ApplyCouponToCartAction::class)->execute($cart, 'VIP-FREE', $buyer);
        $this->assertSame(0, (int) $cart->fresh()->total_amount);

        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        $payment = app(CompleteFreeOrderAction::class)->execute($order);

        $this->assertTrue($payment->isPaid());
        $this->assertTrue($order->fresh()->isPaid());
        $this->assertDatabaseHas('coupon_redemptions', ['order_id' => $order->id, 'coupon_id' => $coupon->id]);
        $this->assertDatabaseHas('vip_subscriptions', ['order_id' => $order->id, 'user_id' => $buyer->id]);
    }

    public function test_vip_coupon_minimum_is_checked_against_vip_items_only(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer);
        $plan = $this->plan();
        $this->addProduct();
        $this->post(route('vip.cart.add', $plan));

        $cart = Cart::query()->where('user_id', $buyer->id)->active()->firstOrFail();
        $this->coupon('VIP-MIN', CouponScopeEnum::VIP, 25, 120000);

        $this->expectException(CouponMinimumOrderAmountNotMetException::class);
        app(ApplyCouponToCartAction::class)->execute($cart, 'VIP-MIN', $buyer);
    }

    public function test_product_order_coupon_remains_ineligible_for_vip_only_cart(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer);
        $plan = $this->plan();
        $this->post(route('vip.cart.add', $plan));
        $cart = Cart::query()->where('user_id', $buyer->id)->active()->firstOrFail();

        $this->coupon('PRODUCT-ONLY', CouponScopeEnum::ORDER);
        $this->expectExceptionMessage('Coupon has no eligible cart items.');
        app(ApplyCouponToCartAction::class)->execute($cart, 'PRODUCT-ONLY', $buyer);
    }

    public function test_vip_coupon_keeps_tax_and_payment_total_consistent(): void
    {
        $settings = $this->createMock(GeneralSettings::class);
        $settings->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null): mixed => match ($key) {
                'tax_enabled' => true,
                'tax_rate_percent' => '10',
                default => $default,
            },
        );
        app()->instance(GeneralSettings::class, $settings);

        $buyer = User::factory()->create();
        $this->actingAs($buyer);
        $plan = $this->plan();
        $this->post(route('vip.cart.add', $plan));
        $cart = Cart::query()->where('user_id', $buyer->id)->active()->firstOrFail();
        $this->coupon('VIP-TAX', CouponScopeEnum::VIP);

        app(ApplyCouponToCartAction::class)->execute($cart, 'VIP-TAX', $buyer);
        $cart->refresh();
        $this->assertSame(75000, (int) $cart->taxable_amount);
        $this->assertSame(7500, (int) $cart->tax_amount);
        $this->assertSame(82500, (int) $cart->total_amount);

        app(RemoveCouponFromCartAction::class)->execute($cart);
        $cart->refresh();
        $this->assertSame(100000, (int) $cart->taxable_amount);
        $this->assertSame(10000, (int) $cart->tax_amount);
        $this->assertSame(110000, (int) $cart->total_amount);

        app(ApplyCouponToCartAction::class)->execute($cart, 'VIP-TAX', $buyer);
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        $this->assertSame(82500, (int) $order->total_amount);
    }

    public function test_fixed_amount_vip_coupon_reduces_subscription_price(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer);
        $plan = $this->plan();
        $this->post(route('vip.cart.add', $plan));

        $coupon = $this->coupon('VIP-FIXED', CouponScopeEnum::VIP);
        $coupon->update(['type' => CouponTypeEnum::FIXED_AMOUNT, 'value' => 30000]);

        $cart = Cart::query()->where('user_id', $buyer->id)->active()->firstOrFail();
        app(ApplyCouponToCartAction::class)->execute($cart, 'VIP-FIXED', $buyer);

        $cart->refresh();
        $this->assertSame(30000, (int) $cart->coupon_discount_amount);
        $this->assertSame(70000, (int) $cart->total_amount);
    }
}
