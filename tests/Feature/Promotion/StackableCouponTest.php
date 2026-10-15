<?php

namespace Tests\Feature\Promotion;

use App\Application\Commerce\Actions\Cart\AddPurchasableToCartAction;
use App\Application\Commerce\Actions\Cart\CreateCartItemAction;
use App\Application\Commerce\Actions\Cart\EnsureCartReadyForCheckoutAction;
use App\Application\Commerce\Actions\Cart\RecalculateCartTotalsAction;
use App\Application\Commerce\Actions\Checkout\CheckoutCartWithWalletAction;
use App\Application\Commerce\Actions\Checkout\CompleteFreeOrderAction;
use App\Application\Commerce\Actions\Checkout\CompleteOrderAfterSuccessfulPaymentAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Application\Commerce\Exceptions\AppliedCouponChangedException;
use App\Application\Financial\Actions\Wallet\CreditWalletAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\DTOs\CreditWalletDTO;
use App\Application\Promotion\Actions\Coupon\ApplyCouponToCartAction;
use App\Application\Promotion\Actions\Coupon\RedeemCouponForOrderAction;
use App\Application\Promotion\Actions\Coupon\ReleaseCouponReservationForOrderAction;
use App\Application\Promotion\Actions\Coupon\RemoveCouponFromCartAction;
use App\Application\Promotion\Actions\Coupon\ReserveCouponForOrderAction;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Promotion\Models\CouponReservation;
use App\Domain\Vip\Models\VipPlan;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Livewire\Front\Commerce\Cart\CartPage;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StackableCouponTest extends TestCase
{
    use RefreshDatabase;

    private function cart(): Cart
    {
        $this->actingAs(User::factory()->create());
        $plan = VipPlan::query()->create([
            'code' => 'stack-vip', 'title' => 'VIP', 'duration_days' => 30,
            'price_toman' => 100000, 'is_active' => true, 'sort_order' => 0,
        ]);
        app(AddPurchasableToCartAction::class)->execute($plan);

        return Cart::query()->where('user_id', auth()->id())->active()->firstOrFail();
    }

    private function coupon(string $code, bool $stackable = false, array $attributes = []): Coupon
    {
        return Coupon::query()->create([
            'code' => $code, 'title' => $code, 'type' => CouponTypeEnum::PERCENTAGE,
            'value' => 25, 'scope' => CouponScopeEnum::VIP, 'status' => CouponStatusEnum::ACTIVE,
            'applies_to_all_users' => true, 'applies_to_addons' => false,
            'is_stackable' => $stackable, ...$attributes,
        ]);
    }

    private function apply(Cart $cart, string $code): Cart
    {
        return app(ApplyCouponToCartAction::class)->execute($cart, $code, $cart->user);
    }

    public static function applicationOrders(): array
    {
        return ['ordinary first' => [['BASE', 'EXTRA']], 'stackable first' => [['EXTRA', 'BASE']]];
    }

    #[DataProvider('applicationOrders')]
    public function test_stackable_coupon_combines_with_an_ordinary_coupon_in_either_entry_order(array $codes): void
    {
        $cart = $this->cart();
        $base = $this->coupon('BASE');
        $this->coupon('EXTRA', true, ['value' => 10]);
        foreach ($codes as $code) {
            $cart = $this->apply($cart, $code);
        }

        $this->assertSame($base->id, $cart->coupon_id);
        $this->assertSame(['BASE', 'EXTRA'], array_column($cart->applied_coupons, 'code'));
        $this->assertSame([25000, 7500], array_column($cart->applied_coupons, 'discount_amount'));
        $this->assertSame(32500, $cart->coupon_discount_amount);
        $this->assertSame(67500, $cart->total_amount);
        $this->assertSame(32500, $cart->items()->firstOrFail()->coupon_discount_amount);
        $this->assertSame($cart->applied_coupons, app(RecalculateCartTotalsAction::class)->execute($cart)->applied_coupons);
    }

    public function test_default_is_off_and_ordinary_codes_keep_replacing_each_other(): void
    {
        $cart = $this->cart();
        $first = $this->coupon('FIRST');
        $this->assertFalse($first->fresh()->is_stackable);
        $this->coupon('SECOND', false, ['value' => 10]);
        $cart = $this->apply($this->apply($cart, 'FIRST'), 'SECOND');
        $this->assertSame(['SECOND'], array_column($cart->applied_coupons, 'code'));
        $this->assertSame(90000, $cart->total_amount);
    }

    public function test_replacing_ordinary_code_preserves_enabled_codes_and_reapplying_never_duplicates(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $this->coupon('NEW', false, ['value' => 50]);
        $this->coupon('EXTRA', true, ['value' => 10]);
        foreach (['BASE', 'EXTRA', 'NEW', 'extra', 'NEW'] as $code) {
            $cart = $this->apply($cart, $code);
        }
        $this->assertSame(['NEW', 'EXTRA'], array_column($cart->applied_coupons, 'code'));
        $this->assertSame(45000, $cart->total_amount);
    }

    public function test_multiple_enabled_codes_work_without_an_ordinary_code(): void
    {
        $cart = $this->cart();
        $this->coupon('ONE', true, ['value' => 10]);
        $this->coupon('TWO', true, ['value' => 20]);
        $this->coupon('THREE', true, ['type' => CouponTypeEnum::FIXED_AMOUNT, 'value' => 1000]);
        foreach (['ONE', 'TWO', 'THREE'] as $code) {
            $cart = $this->apply($cart, $code);
        }
        $this->assertSame([10000, 18000, 1000], array_column($cart->applied_coupons, 'discount_amount'));
        $this->assertSame(71000, $cart->total_amount);
    }

    public function test_failed_code_does_not_remove_previous_codes_or_change_item_totals(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $this->coupon('INVALID', true, ['expires_at' => now()->subMinute()]);
        $cart = $this->apply($cart, 'BASE');
        $before = $cart->applied_coupons;
        try {
            $this->apply($cart, 'INVALID');
            $this->fail('Expired coupon must fail.');
        } catch (DomainException $exception) {
            $this->assertSame('Coupon is not currently usable.', $exception->getMessage());
        }
        $this->assertSame($before, $cart->fresh()->applied_coupons);
        $this->assertSame(75000, $cart->fresh()->total_amount);
        $this->assertSame(75000, $cart->items()->firstOrFail()->final_total_amount);
    }

    public function test_admin_can_create_enable_disable_and_validate_the_new_setting(): void
    {
        $role = Role::factory()->create(['is_super_admin' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $data = [
            'code' => 'ADMIN', 'title' => 'Admin coupon', 'type' => 'percentage', 'value' => 10,
            'status' => 'active', 'scope' => 'vip', 'applies_to_all_users' => 1,
            'applies_to_addons' => 0, 'is_floating_box_enabled' => 0,
        ];
        $this->get(route('admin.coupons.create'))->assertOk()->assertSee('name="is_stackable"', false);
        $this->post(route('admin.coupons.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $coupon = Coupon::query()->where('code', 'ADMIN')->firstOrFail();
        $this->assertFalse($coupon->is_stackable);
        $this->put(route('admin.coupons.update', $coupon), [...$data, 'is_stackable' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue($coupon->fresh()->is_stackable);
        $this->get(route('admin.coupons.edit', $coupon))->assertOk()->assertSee('checked', false);
        $this->put(route('admin.coupons.update', $coupon), [...$data, 'is_stackable' => 'invalid'])
            ->assertSessionHasErrors('is_stackable');
        $this->assertTrue($coupon->fresh()->is_stackable);
        $this->put(route('admin.coupons.update', $coupon), [...$data, 'is_stackable' => 0])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertFalse($coupon->fresh()->is_stackable);
    }

    public function test_livewire_lists_both_discounts_and_removes_only_the_selected_code(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $this->coupon('EXTRA', true, ['value' => 10]);
        Livewire::test(CartPage::class)->set('couponCode', 'BASE')->call('applyCoupon')
            ->set('couponCode', 'EXTRA')->call('applyCoupon')
            ->assertSee('BASE')->assertSee('EXTRA')->assertSet('couponCode', null)
            ->call('removeCoupon', 'EXTRA')->assertDontSee('EXTRA')->assertSee('BASE')
            ->assertSet('checkoutError', null);
        $this->assertSame(['BASE'], array_column($cart->fresh()->applied_coupons, 'code'));
        $this->assertSame(75000, $cart->fresh()->total_amount);
        app(RemoveCouponFromCartAction::class)->execute($cart);
        $this->assertSame([], $cart->fresh()->applied_coupons);
        $this->assertSame(100000, $cart->fresh()->total_amount);
    }

    public function test_removing_ordinary_code_recalculates_the_remaining_coupon(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $this->coupon('EXTRA', true, ['value' => 10]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $cart = app(RemoveCouponFromCartAction::class)->execute($cart, 'base');
        $this->assertSame(['EXTRA'], array_column($cart->applied_coupons, 'code'));
        $this->assertSame(10000, $cart->coupon_discount_amount);
        $this->assertSame(90000, $cart->total_amount);
    }

    public function test_caps_minimum_and_tax_remain_consistent_with_stacking(): void
    {
        $settings = $this->createMock(GeneralSettings::class);
        $settings->method('get')->willReturnCallback(static fn (string $key, mixed $default = null): mixed => match ($key) {
            'tax_enabled' => true, 'tax_rate_percent' => '10', default => $default,
        });
        app()->instance(GeneralSettings::class, $settings);
        $cart = $this->cart();
        $this->coupon('BASE', false, ['value' => 50]);
        $this->coupon('EXTRA', true, ['value' => 50, 'max_discount_amount' => 10000, 'minimum_order_amount' => 100000]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $this->assertSame([50000, 10000], array_column($cart->applied_coupons, 'discount_amount'));
        $this->assertSame(40000, $cart->taxable_amount);
        $this->assertSame(4000, $cart->tax_amount);
        $this->assertSame(44000, $cart->total_amount);
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        $this->assertSame(44000, $order->total_amount);
        $this->assertSame(60000, $order->items()->firstOrFail()->coupon_discount_amount);
    }

    public function test_fixed_discounts_are_capped_at_remaining_amount_and_free_checkout_redeems_both(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE', false, ['type' => CouponTypeEnum::FIXED_AMOUNT, 'value' => 80000]);
        $this->coupon('EXTRA', true, ['type' => CouponTypeEnum::FIXED_AMOUNT, 'value' => 50000]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $this->assertSame([80000, 20000], array_column($cart->applied_coupons, 'discount_amount'));
        $this->assertSame(0, $cart->total_amount);
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        app(CompleteFreeOrderAction::class)->execute($order);
        $this->assertSame(2, DB::table('coupon_redemptions')->where('order_id', $order->id)->count());
        $this->assertSame(100000, (int) DB::table('coupon_redemptions')->where('order_id', $order->id)->sum('discount_amount'));
    }

    public function test_each_coupon_is_reserved_and_redeemed_once_with_its_own_amount_and_code_snapshot(): void
    {
        $cart = $this->cart();
        $base = $this->coupon('BASE', false, ['usage_limit' => 1, 'usage_limit_per_user' => 1]);
        $extra = $this->coupon('EXTRA', true, ['value' => 10, 'usage_limit' => 1, 'usage_limit_per_user' => 1]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        $this->assertSame($cart->applied_coupons, $order->applied_coupons);
        app(ReserveCouponForOrderAction::class)->execute($order);
        $this->assertSame(2, $order->couponReservations()->count());
        $this->assertDatabaseHas('coupon_reservations', ['order_id' => $order->id, 'coupon_id' => $extra->id, 'discount_amount' => 7500]);
        $extra->update(['code' => 'RENAMED']);
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($order);
        app(RedeemCouponForOrderAction::class)->execute($order);
        $this->assertSame(1, $base->fresh()->used_count);
        $this->assertSame(1, $extra->fresh()->used_count);
        $this->assertSame(2, DB::table('coupon_redemptions')->where('order_id', $order->id)->count());
        $this->assertDatabaseHas('coupon_redemptions', [
            'order_id' => $order->id, 'coupon_id' => $extra->id, 'code' => 'EXTRA', 'discount_amount' => 7500, 'eligible_amount' => 75000,
        ]);
        $this->assertSame(2, $order->couponReservations()->where('status', 'redeemed')->count());
    }

    public function test_cancellation_releases_all_reservations_and_retry_reactivates_each_once(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE', false, ['usage_limit' => 1]);
        $this->coupon('EXTRA', true, ['usage_limit' => 1]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        app(ReleaseCouponReservationForOrderAction::class)->execute($order, 'cancelled');
        $this->assertSame(2, $order->couponReservations()->where('status', 'released')->count());
        app(ReserveCouponForOrderAction::class)->execute($order);
        $this->assertSame(2, $order->couponReservations()->where('status', 'active')->count());
        $this->assertSame(2, $order->couponReservations()->count());
    }

    public function test_secondary_coupon_reservation_limit_rolls_back_entire_order_and_all_reservations(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $extra = $this->coupon('EXTRA', true, ['usage_limit' => 1]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        CouponReservation::query()->create([
            'coupon_id' => $extra->id, 'code' => 'EXTRA', 'status' => 'active', 'expires_at' => now()->addMinutes(10),
        ]);
        try {
            app(CreateOrderFromCartAction::class)->execute($cart);
            $this->fail('Capacity must be enforced for the secondary coupon.');
        } catch (DomainException $exception) {
            $this->assertSame('Coupon reservation limit reached.', $exception->getMessage());
        }
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(1, CouponReservation::query()->count());
        $this->assertTrue($cart->fresh()->isEditable());
    }

    public function test_secondary_coupon_expiration_requires_checkout_review_and_preserves_the_valid_coupon(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $extra = $this->coupon('EXTRA', true);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $extra->update(['expires_at' => now()->subMinute()]);
        try {
            app(EnsureCartReadyForCheckoutAction::class)->execute($cart);
            $this->fail('Checkout must surface loss of a secondary discount.');
        } catch (AppliedCouponChangedException $exception) {
            $this->assertSame('BASE + EXTRA', $exception->previousCouponCode);
            $this->assertSame('BASE', $exception->currentCouponCode);
        }
        $this->assertSame(['BASE'], array_column($cart->fresh()->applied_coupons, 'code'));
        $this->assertSame(75000, $cart->fresh()->total_amount);
    }

    public function test_secondary_code_change_is_detected_even_when_total_is_unchanged(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $extra = $this->coupon('EXTRA', true);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $extra->update(['code' => 'NEW-CODE']);
        $this->expectException(AppliedCouponChangedException::class);
        app(EnsureCartReadyForCheckoutAction::class)->execute($cart);
    }

    public function test_direct_order_creation_does_not_silently_drop_an_invalid_secondary_coupon(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $extra = $this->coupon('EXTRA', true);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $extra->delete();
        $this->expectException(AppliedCouponChangedException::class);
        app(CreateOrderFromCartAction::class)->execute($cart);
    }

    public function test_disabling_stacking_removes_the_extra_ordinary_coupon_on_recalculation(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $extra = $this->coupon('EXTRA', true);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $extra->update(['is_stackable' => false]);
        $cart = app(RecalculateCartTotalsAction::class)->execute($cart);
        $this->assertSame(['BASE'], array_column($cart->applied_coupons, 'code'));
        $this->assertSame(75000, $cart->total_amount);
    }

    public function test_legacy_single_coupon_cart_and_order_work_without_backfilling_snapshots(): void
    {
        $cart = $this->cart();
        $coupon = $this->coupon('LEGACY');
        $cart = $this->apply($cart, 'LEGACY');
        $cart->update(['applied_coupons' => null]);
        $this->assertCount(1, $cart->appliedCouponSnapshots());
        $cart = app(RecalculateCartTotalsAction::class)->execute($cart);
        $this->assertSame(75000, $cart->total_amount);
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        $order->update(['applied_coupons' => null]);
        app(RedeemCouponForOrderAction::class)->execute($order);
        $this->assertDatabaseHas('coupon_redemptions', ['order_id' => $order->id, 'coupon_id' => $coupon->id, 'discount_amount' => 25000]);
    }

    private function product(): ProductSaleItem
    {
        $now = now();
        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Stack Store', 'slug' => 'stack-store', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId, 'title' => 'Stack Product', 'slug' => 'stack-product',
            'type' => 'digital', 'structure' => 'simple', 'status' => 'active', 'published_at' => $now,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $item = ProductSaleItem::query()->create([
            'product_id' => $productId, 'sku' => 'STACK-SKU', 'base_price' => 50000, 'status' => 'available',
        ]);
        app(AddPurchasableToCartAction::class)->execute($item);

        return $item;
    }

    public function test_vip_and_product_scopes_remain_independent_in_a_mixed_cart(): void
    {
        $cart = $this->cart();
        $this->product();
        $this->coupon('VIP');
        $this->coupon('PRODUCT', true, ['scope' => CouponScopeEnum::ORDER, 'value' => 10]);
        $cart = $this->apply($this->apply($cart, 'VIP'), 'PRODUCT');
        $this->assertSame([25000, 5000], array_column($cart->applied_coupons, 'discount_amount'));
        $items = $cart->items()->get()->keyBy('purchasable_type');
        $this->assertSame(25000, $items['vip_plan']->coupon_discount_amount);
        $this->assertSame(5000, $items['product_sale_item']->coupon_discount_amount);
        $this->assertSame(120000, $cart->total_amount);
    }

    public function test_product_sale_discount_and_addon_exclusion_are_preserved_for_each_coupon(): void
    {
        $cart = $this->cart();
        $saleItem = $this->product();
        ProductSaleItemDiscount::query()->create(['product_sale_item_id' => $saleItem->id, 'discounted_price' => 40000]);
        $addon = ProductAddon::query()->create([
            'product_id' => $saleItem->product_id, 'title' => 'Addon', 'price' => 10000, 'is_active' => true,
        ]);
        $parent = $cart->items()->where('purchasable_type', 'product_sale_item')->firstOrFail();
        app(CreateCartItemAction::class)->execute($cart, $addon, 1, $parent);
        $this->coupon('BASE', false, ['scope' => CouponScopeEnum::PRODUCTS]);
        $base = Coupon::query()->where('code', 'BASE')->firstOrFail();
        $base->targets()->create(['target_type' => 'product', 'target_id' => $saleItem->product_id]);
        $this->coupon('EXTRA', true, ['scope' => CouponScopeEnum::STORE, 'value' => 10]);
        $extra = Coupon::query()->where('code', 'EXTRA')->firstOrFail();
        $extra->targets()->create(['target_type' => Store::class, 'target_id' => $saleItem->product->store_id]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $items = $cart->items()->get()->keyBy('purchasable_type');
        $this->assertSame(10000, $items['product_sale_item']->discount_amount);
        $this->assertSame(13000, $items['product_sale_item']->coupon_discount_amount);
        $this->assertSame(0, $items['product_addon']->coupon_discount_amount);
        $this->assertSame(137000, $cart->total_amount);
    }

    public function test_wallet_checkout_charges_the_combined_total_and_records_both_uses(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $this->coupon('EXTRA', true, ['value' => 10]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $wallet = app(GetOrCreateWalletAction::class)->execute($cart->user);
        app(CreditWalletAction::class)->execute(new CreditWalletDTO(
            wallet: $wallet, type: WalletTransactionTypeEnum::TOP_UP, amount: 100000,
            idempotencyKey: 'stack-wallet-credit', actor: $cart->user,
        ));
        $payment = app(CheckoutCartWithWalletAction::class)->execute($cart, $cart->user);
        $this->assertTrue($payment->isPaid());
        $this->assertSame(67500, $payment->amount);
        $this->assertSame(32500, $wallet->fresh()->balance);
        $this->assertSame(2, DB::table('coupon_redemptions')->count());
    }

    public function test_per_user_capacity_is_enforced_for_the_secondary_coupon_at_reservation(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $extra = $this->coupon('EXTRA', true, ['usage_limit_per_user' => 1]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        CouponReservation::query()->create([
            'coupon_id' => $extra->id, 'user_id' => $cart->user_id, 'code' => 'EXTRA',
            'status' => 'active', 'expires_at' => now()->addMinutes(10),
        ]);
        try {
            app(CreateOrderFromCartAction::class)->execute($cart);
            $this->fail('Secondary coupon must enforce per-user capacity.');
        } catch (DomainException $exception) {
            $this->assertSame('Coupon per user reservation limit reached.', $exception->getMessage());
        }
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(1, CouponReservation::query()->count());
    }

    public function test_stacking_does_not_bypass_user_restrictions_or_minimum_order_amount(): void
    {
        $cart = $this->cart();
        $this->coupon('BASE');
        $extra = $this->coupon('EXTRA', true, ['applies_to_all_users' => false]);
        $cart = $this->apply($cart, 'BASE');
        try {
            $this->apply($cart, 'EXTRA');
            $this->fail('Restricted user must be rejected.');
        } catch (DomainException $exception) {
            $this->assertSame('Coupon is not allowed for this user.', $exception->getMessage());
        }
        $extra->update(['applies_to_all_users' => true, 'minimum_order_amount' => 100001]);
        try {
            $this->apply($cart, 'EXTRA');
            $this->fail('Minimum order amount must be enforced.');
        } catch (DomainException $exception) {
            $this->assertSame('Coupon minimum order amount not met.', $exception->getMessage());
        }
        $this->assertSame(['BASE'], array_column($cart->fresh()->applied_coupons, 'code'));
        $this->assertSame(75000, $cart->fresh()->total_amount);
    }

    public function test_stacked_allocation_rounding_keeps_item_order_and_redemption_totals_equal(): void
    {
        $cart = $this->cart();
        $secondPlan = VipPlan::query()->create([
            'code' => 'second-vip', 'title' => 'Second VIP', 'duration_days' => 30,
            'price_toman' => 100001, 'is_active' => true, 'sort_order' => 1,
        ]);
        app(AddPurchasableToCartAction::class)->execute($secondPlan);
        $this->coupon('BASE', false, ['value' => 33]);
        $this->coupon('EXTRA', true, ['value' => 50]);
        $cart = $this->apply($this->apply($cart, 'BASE'), 'EXTRA');
        $this->assertSame(133000, $cart->coupon_discount_amount);
        $this->assertSame(67001, $cart->total_amount);
        $this->assertSame($cart->total_amount, (int) $cart->items()->sum('final_total_amount'));
        $this->assertSame($cart->coupon_discount_amount, array_sum(array_column($cart->applied_coupons, 'discount_amount')));
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        app(RedeemCouponForOrderAction::class)->execute($order);
        $this->assertSame($order->coupon_discount_amount, (int) $order->items()->sum('coupon_discount_amount'));
        $this->assertSame($order->coupon_discount_amount, (int) DB::table('coupon_redemptions')->sum('discount_amount'));
    }
}
