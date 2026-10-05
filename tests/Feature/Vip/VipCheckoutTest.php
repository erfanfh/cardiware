<?php

namespace Tests\Feature\Vip;

use App\Application\Commerce\Actions\Cart\AddPurchasableToCartAction;
use App\Application\Commerce\Actions\Checkout\CompleteFreeOrderAction;
use App\Application\Commerce\Actions\Checkout\CompleteOrderAfterSuccessfulPaymentAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Application\Commerce\Actions\Order\UpdateOrderStatusAction;
use App\Application\Vip\Actions\RevokeVipSubscriptionsForOrderAction;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use App\Http\Middleware\ForceTrailingSlash;
use App\Livewire\Front\Commerce\Cart\HeaderCartDropdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class VipCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function plan(array $attributes = []): VipPlan
    {
        return VipPlan::query()->create(array_replace([
            'code' => 'vip-month',
            'title' => 'پلن ماهانه',
            'description' => 'اعتبار یک ماهه',
            'duration_days' => 30,
            'price_toman' => 100000,
            'is_active' => true,
            'sort_order' => 0,
        ], $attributes));
    }

    private function createAndPayCurrentCart(): Order
    {
        $cart = Cart::query()->active()->latest('id')->firstOrFail();
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($order);

        return $order->refresh();
    }

    public function test_only_active_plans_are_listed_and_guest_must_sign_in_to_buy(): void
    {
        $active = $this->plan();
        $this->plan(['code' => 'vip-hidden', 'title' => 'پلن مخفی', 'is_active' => false]);

        $this->withoutMiddleware(ForceTrailingSlash::class)
            ->get(route('vip.index'))
            ->assertOk()
            ->assertSee($active->title)
            ->assertDontSee('پلن مخفی');

        $this->post(route('vip.cart.add', $active))->assertRedirect(route('login'));
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_paid_order_records_vip_item_and_renews_without_overlap(): void
    {
        $user = User::factory()->create();
        $plan = $this->plan();
        $this->actingAs($user);

        $this->post(route('vip.cart.add', $plan))->assertRedirect(route('cart.index'));
        $this->post(route('vip.cart.add', $plan))->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('cart_items', 1);
        $cartItem = Cart::query()->active()->firstOrFail()->items()->firstOrFail();
        $this->assertSame('اشتراک VIP - پلن ماهانه', data_get($cartItem->snapshot, 'title'));
        $this->assertSame(VipPlan::IMAGE_URL, data_get($cartItem->snapshot, 'image_url'));

        $firstOrder = $this->createAndPayCurrentCart();
        $firstItem = $firstOrder->items()->firstOrFail();
        $firstSubscription = VipSubscription::query()->where('order_item_id', $firstItem->id)->firstOrFail();
        $this->assertSame('vip_plan', $firstItem->purchasable_type);
        $this->assertSame(30, data_get($firstItem->snapshot, 'duration_days'));
        $this->assertSame('اشتراک VIP - پلن ماهانه', data_get($firstItem->snapshot, 'title'));
        $this->assertSame(VipPlan::IMAGE_URL, data_get($firstItem->snapshot, 'image_url'));
        $this->assertTrue($user->hasActiveVipSubscription());
        $this->withoutMiddleware(ForceTrailingSlash::class)
            ->get(route('vip.index'))
            ->assertOk()
            ->assertSee('تاریخ و ساعت پایان اعتبار')
            ->assertSee(jalali_date($firstSubscription->expires_at, 'j F Y').' ساعت '.jalali_date($firstSubscription->expires_at, 'H:i'));

        $this->post(route('vip.cart.add', $plan))->assertRedirect(route('cart.index'));
        $secondCart = Cart::query()->active()->latest('id')->firstOrFail();
        $secondOrder = app(CreateOrderFromCartAction::class)->execute($secondCart);
        $plan->update(['duration_days' => 60]);
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($secondOrder);
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($secondOrder);

        $secondSubscription = VipSubscription::query()->where('order_id', $secondOrder->id)->firstOrFail();
        $this->assertDatabaseCount('vip_subscriptions', 2);
        $this->assertSame(30, $secondSubscription->duration_days);
        $this->assertSame(
            $firstSubscription->expires_at->toDateTimeString(),
            $secondSubscription->starts_at->toDateTimeString(),
        );
        $this->assertSame(
            $secondSubscription->starts_at->copy()->addDays(30)->toDateTimeString(),
            $secondSubscription->expires_at->toDateTimeString(),
        );

        app(RevokeVipSubscriptionsForOrderAction::class)->execute($firstOrder);
        $this->assertNotNull($firstSubscription->fresh()->revoked_at);
        $this->assertTrue($user->hasActiveVipSubscription());
        $this->assertTrue($secondSubscription->fresh()->starts_at->lte(now()));
    }

    public function test_inactive_plan_cannot_be_added_to_cart(): void
    {
        $this->actingAs(User::factory()->create());
        $plan = $this->plan(['is_active' => false]);

        $this->post(route('vip.cart.add', $plan))->assertNotFound();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_reconfirmed_order_restores_revoked_subscription(): void
    {
        $user = User::factory()->create();
        $plan = $this->plan();
        $this->actingAs($user);

        $this->post(route('vip.cart.add', $plan));
        $order = $this->createAndPayCurrentCart();
        $action = app(UpdateOrderStatusAction::class);

        $action->execute($order, OrderStatusEnum::FAILED);
        $this->assertFalse($user->hasActiveVipSubscription());

        $action->execute($order->fresh(), OrderStatusEnum::PAID);
        $this->assertTrue($user->hasActiveVipSubscription());
        $this->assertDatabaseCount('vip_subscriptions', 1);
        $this->assertNull(VipSubscription::query()->firstOrFail()->revoked_at);
    }

    public function test_product_and_vip_plan_can_share_a_cart_and_paid_order(): void
    {
        $user = User::factory()->create();
        $plan = $this->plan();
        $this->actingAs($user);
        $now = now();

        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Test Store',
            'slug' => 'test-store',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId,
            'title' => 'Test Product',
            'slug' => 'test-product',
            'type' => 'digital',
            'structure' => 'simple',
            'status' => 'active',
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $saleItemId = DB::table('product_sale_items')->insertGetId([
            'product_id' => $productId,
            'sku' => 'VIP-MIXED-CART',
            'base_price' => 50000,
            'status' => 'available',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        app(AddPurchasableToCartAction::class)->execute(ProductSaleItem::query()->findOrFail($saleItemId));
        $this->post(route('vip.cart.add', $plan))->assertRedirect(route('cart.index'));

        $cart = Cart::query()->active()->firstOrFail();
        $this->assertCount(2, $cart->items);
        $this->withoutMiddleware(ForceTrailingSlash::class)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('اشتراک VIP')
            ->assertSee('Test Product');
        $order = $this->createAndPayCurrentCart();

        $this->assertCount(2, $order->items);
        $this->assertSame(150000, (int) $order->subtotal_amount);
        $this->assertDatabaseHas('vip_subscriptions', ['order_id' => $order->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('purchase_accesses', ['order_id' => $order->id, 'user_id' => $user->id]);
    }

    public function test_free_vip_order_receipt_shows_subscription_instead_of_downloads(): void
    {
        $user = User::factory()->create();
        $plan = $this->plan(['price_toman' => 0]);
        $this->actingAs($user);

        $this->post(route('vip.cart.add', $plan));
        $order = app(CreateOrderFromCartAction::class)->execute(Cart::query()->active()->firstOrFail());
        $payment = app(CompleteFreeOrderAction::class)->execute($order);

        $this->get(route('payment.result', $payment->public_token))
            ->assertOk()
            ->assertSee('اشتراک VIP - پلن ماهانه')
            ->assertSee(VipPlan::IMAGE_URL)
            ->assertSee('اعتبار اشتراک VIP')
            ->assertSee('مشاهده اشتراک VIP')
            ->assertDontSee('مشاهده دانلودها');
    }

    public function test_legacy_vip_items_show_the_new_title_and_image_everywhere(): void
    {
        $this->actingAs(User::factory()->create());
        $plan = $this->plan(['price_toman' => 0]);
        $this->post(route('vip.cart.add', $plan));

        $cart = Cart::query()->active()->firstOrFail();
        $cartItem = $cart->items()->firstOrFail();
        $legacySnapshot = $cartItem->snapshot;
        $legacySnapshot['title'] = 'اشتراک VIP پلن ماهانه';
        unset($legacySnapshot['plan_title'], $legacySnapshot['image_url']);
        $cartItem->update(['snapshot' => $legacySnapshot]);

        $this->withoutMiddleware(ForceTrailingSlash::class)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('اشتراک VIP - پلن ماهانه')
            ->assertSee(VipPlan::IMAGE_URL);

        Livewire::test(HeaderCartDropdown::class)
            ->call('reloadCart', false)
            ->assertSet('summary.items.0.title', 'اشتراک VIP - پلن ماهانه')
            ->assertSet('summary.items.0.image_url', VipPlan::IMAGE_URL);

        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        $order->items()->firstOrFail()->update(['snapshot' => $legacySnapshot]);
        $payment = app(CompleteFreeOrderAction::class)->execute($order);

        $this->withoutMiddleware(ForceTrailingSlash::class)
            ->get(route('payment.result', $payment->public_token))
            ->assertOk()
            ->assertSee('اشتراک VIP - پلن ماهانه')
            ->assertSee(VipPlan::IMAGE_URL);

        $this->withoutMiddleware(ForceTrailingSlash::class)
            ->get(route('account.orders'))
            ->assertOk()
            ->assertSee('اشتراک VIP - پلن ماهانه')
            ->assertSee(VipPlan::IMAGE_URL);
    }
}
