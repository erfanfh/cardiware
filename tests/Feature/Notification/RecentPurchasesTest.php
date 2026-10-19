<?php

namespace Tests\Feature\Notification;

use App\Application\Commerce\Actions\Order\ChangeOrderStatusAction;
use App\Application\Commerce\Actions\Order\MarkOrderAsPaidAction;
use App\Application\Notification\GetRecentPurchasesAction;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\NotificationsSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class RecentPurchasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeTime();
        BaseSettings::clearResolvedGroups();
        Event::fake([OrderPaidEvent::class]);
    }

    public function test_public_feed_shows_the_full_name_without_serializing_unrelated_private_fields(): void
    {
        $order = $this->purchase();
        $this->item($order, $order->items->first()->product);
        $response = $this->getJson(route('notifications.recent-purchases'));

        $response->assertOk()->assertJsonCount(1, 'purchases')
            ->assertJsonPath('purchases.0.buyer', 'علیرضا محمدی')
            ->assertJsonPath('purchases.0.product', 'پچ فیفا')
            ->assertJsonPath('purchases.0.url', $order->items->first()->product->url)
            ->assertJsonPath('purchases.0.purchased_at', now()->getTimestamp())
            ->assertJsonPath('purchases.0.expires_at', now()->addMinutes(30)->getTimestamp());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $response->json('purchases.0.id'));
        $this->assertSame(['id', 'buyer', 'product', 'url', 'purchased_at', 'expires_at'], array_keys($response->json('purchases.0')));
        foreach ([$order->order_number, $order->user->email, $order->user->mobile, 'PRIVATE-METADATA'] as $private) {
            $this->assertStringNotContainsString($private, $response->getContent());
        }
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->headers->getCookies());
    }

    #[DataProvider('excludedPurchases')]
    public function test_ineligible_purchases_are_not_public(string $reason): void
    {
        $order = $this->purchase();
        $product = $order->items->first()->product;
        match ($reason) {
            'pending' => $order->update(['status' => OrderStatusEnum::PENDING_PAYMENT]),
            'refunded' => $order->update(['status' => OrderStatusEnum::REFUNDED]),
            'free' => $order->update(['total_amount' => 0]),
            'legacy-or-manual' => $order->update(['metadata' => []]),
            'expired' => $order->update(['paid_at' => now()->subMinutes(31)]),
            'future' => $order->update(['paid_at' => now()->addMinute()]),
            'inactive-product' => $product->update(['status' => ProductStatusEnum::INACTIVE]),
            'unpublished-product' => $product->update(['published_at' => now()->addDay()]),
            'deleted-product' => $product->delete(),
            'deleted-user' => $order->user->delete(),
            'inactive-user' => $order->user->update(['status' => 'inactive']),
            'free-item' => $order->items()->update(['final_total_amount' => 0]),
            'vip-only' => $order->items()->update(['product_id' => null, 'purchasable_type' => 'vip_plan']),
        };

        $this->getJson(route('notifications.recent-purchases'))->assertOk()->assertExactJson(['purchases' => []]);
    }

    public static function excludedPurchases(): array
    {
        return array_map(fn ($reason) => [$reason], [
            'pending', 'refunded', 'free', 'legacy-or-manual', 'expired', 'future',
            'inactive-product', 'unpublished-product', 'deleted-product', 'deleted-user', 'inactive-user', 'free-item', 'vip-only',
        ]);
    }

    public function test_missing_or_contact_shaped_first_names_use_an_anonymous_label(): void
    {
        $order = $this->purchase();
        foreach (['', '09123456789', 'buyer@example.test', '<script>'] as $name) {
            $order->user->update(['first_name' => $name]);
            Cache::forget(GetRecentPurchasesAction::CACHE_KEY);
            $this->assertSame('یکی از کاربران', app(GetRecentPurchasesAction::class)->execute()[0]['buyer']);
        }
    }

    public function test_shared_feed_is_cached_and_rechecks_refunds_after_thirty_seconds(): void
    {
        $order = $this->purchase();
        $feed = app(GetRecentPurchasesAction::class);
        $this->assertCount(1, $feed->execute());
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertCount(1, $feed->execute());
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();

        $order->update(['status' => OrderStatusEnum::REFUNDED]);
        $this->travel(31)->seconds();
        $this->assertSame([], $feed->execute());
    }

    public function test_latest_paid_time_determines_order_even_if_an_older_order_pays_later(): void
    {
        $order = $this->purchase();
        $other = $this->purchase();
        $other->update(['paid_at' => now()->subMinute()]);
        $feed = app(GetRecentPurchasesAction::class)->execute();
        $this->assertCount(2, $feed);
        $this->assertSame(now()->getTimestamp(), $feed[0]['purchased_at']);
        $this->assertSame(hash_hmac('sha256', (string) $order->id, config('app.key')), $feed[0]['id']);
    }

    public function test_feed_is_bounded_and_can_be_disabled_without_reading_orders(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $this->purchase();
        }
        $this->assertCount(5, app(GetRecentPurchasesAction::class)->execute());
        app(NotificationsSettings::class)->set('recent_purchases_enabled', false);
        $this->getJson(route('notifications.recent-purchases'))->assertExactJson(['purchases' => []]);
    }

    public function test_checkout_marks_eligibility_idempotently_and_manual_transitions_clear_it(): void
    {
        $order = $this->purchase();
        $order->update(['status' => OrderStatusEnum::PENDING_PAYMENT, 'paid_at' => null, 'metadata' => ['source' => 'checkout']]);
        $action = app(MarkOrderAsPaidAction::class);
        $order = $action->execute($order);
        $paidAt = $order->paid_at->toDateTimeString();
        $this->assertTrue($order->metadata['purchase_notification_eligible']);
        $this->assertSame('checkout', $order->metadata['source']);
        $this->assertSame($paidAt, $action->execute($order)->paid_at->toDateTimeString());

        $manual = app(ChangeOrderStatusAction::class);
        $order = $manual->execute($order, OrderStatusEnum::CANCELLED);
        $order = $manual->execute($order, OrderStatusEnum::PAID);
        $this->assertFalse($order->metadata['purchase_notification_eligible']);
        $this->assertSame([], app(GetRecentPurchasesAction::class)->execute());
    }

    public function test_rolled_back_checkout_does_not_leave_a_public_purchase(): void
    {
        $order = $this->purchase();
        $order->update(['status' => OrderStatusEnum::PENDING_PAYMENT, 'paid_at' => null, 'metadata' => []]);
        try {
            DB::transaction(function () use ($order): void {
                app(MarkOrderAsPaidAction::class)->execute($order);
                throw new RuntimeException('Rollback checkout');
            });
        } catch (RuntimeException) {
            $this->assertSame(OrderStatusEnum::PENDING_PAYMENT, $order->fresh()->status);
        }
        $this->assertSame([], app(GetRecentPurchasesAction::class)->execute());
    }

    public function test_component_is_suppressed_on_checkout_and_private_pages_and_when_disabled(): void
    {
        $this->assertStringContainsString('data-recent-purchases', view('components.front.recent-purchases')->render());
        foreach (['cart.index', 'payment.result', 'account.orders', 'seller.dashboard', 'auth.index', 'preview.product', 'launcher.checkout.entry'] as $name) {
            $route = (new Route('GET', '/', fn () => null))->name($name);
            request()->setRouteResolver(fn () => $route);
            $this->assertStringNotContainsString('data-recent-purchases', view('components.front.recent-purchases')->render());
        }
        request()->setRouteResolver(fn () => null);
        app(NotificationsSettings::class)->set('recent_purchases_enabled', false);
        $this->assertStringNotContainsString('data-recent-purchases', view('components.front.recent-purchases')->render());
    }

    public function test_admin_can_manage_the_switch_and_it_controls_both_the_feed_and_component(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $settings = app(NotificationsSettings::class);
        $this->assertTrue($settings->recentPurchasesEnabled());
        $this->actingAs($admin)->get(route('admin.settings.notifications'))
            ->assertOk()->assertSee('recent_purchases_enabled')->assertSee('اعلان خریدهای اخیر')
            ->assertViewHas('recentPurchasesEnabled', true);

        $this->purchase();
        $this->getJson(route('notifications.recent-purchases'))->assertJsonCount(1, 'purchases');
        $settings->set('order_paid_sms_enabled', true);
        $url = route('admin.settings.notifications.recent-purchases.update');
        $this->put($url, ['recent_purchases_enabled' => '0', 'order_paid_sms_enabled' => '0'])
            ->assertRedirect(route('admin.settings.notifications'))->assertSessionHasNoErrors();
        BaseSettings::clearResolvedGroups();
        $this->assertFalse($settings->recentPurchasesEnabled());
        $this->assertTrue($settings->get('order_paid_sms_enabled'));
        $this->assertDatabaseHas('settings', ['group' => 'notifications', 'key' => 'recent_purchases_enabled']);
        $this->assertFalse(Cache::has(GetRecentPurchasesAction::CACHE_KEY));
        $this->getJson(route('notifications.recent-purchases'))->assertExactJson(['purchases' => []]);
        $this->assertStringNotContainsString('data-recent-purchases', view('components.front.recent-purchases')->render());

        $this->put($url, ['recent_purchases_enabled' => '1'])
            ->assertRedirect(route('admin.settings.notifications'))->assertSessionHasNoErrors();
        BaseSettings::clearResolvedGroups();
        $this->assertTrue($settings->recentPurchasesEnabled());
        $this->getJson(route('notifications.recent-purchases'))->assertJsonCount(1, 'purchases');
        $this->assertStringContainsString('data-recent-purchases', view('components.front.recent-purchases')->render());
        $this->assertDatabaseCount('notification_event_settings', 0);
    }

    public function test_admin_switch_requires_general_settings_permission(): void
    {
        $user = User::factory()->create();
        Gate::define('admin.display', fn () => true);
        Gate::define('setting.general', fn () => false);
        $this->actingAs($user)->put(route('admin.settings.notifications.recent-purchases.update'), ['recent_purchases_enabled' => 0])
            ->assertForbidden();
        $this->assertDatabaseMissing('settings', ['group' => 'notifications', 'key' => 'recent_purchases_enabled']);
    }

    public function test_admin_switch_rejects_missing_or_invalid_values(): void
    {
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $this->actingAs($admin);
        $url = route('admin.settings.notifications.recent-purchases.update');
        foreach ([[], ['recent_purchases_enabled' => 'invalid']] as $payload) {
            $this->put($url, $payload)->assertSessionHasErrors('recent_purchases_enabled');
        }
        $this->assertDatabaseMissing('settings', ['group' => 'notifications', 'key' => 'recent_purchases_enabled']);
        $this->assertTrue(app(NotificationsSettings::class)->recentPurchasesEnabled());
    }

    private function purchase(): Order
    {
        $user = User::factory()->create(['first_name' => 'علیرضا', 'last_name' => 'محمدی']);
        $store = Store::query()->create(['title' => 'Store', 'slug' => 'store-'.uniqid(), 'status' => StoreStatusEnum::ACTIVE]);
        $product = Product::query()->create([
            'store_id' => $store->id, 'title' => 'پچ فیفا', 'slug' => 'fifa-'.uniqid(),
            'type' => ProductTypeEnum::DIGITAL, 'structure' => ProductStructureEnum::SIMPLE,
            'status' => ProductStatusEnum::ACTIVE, 'published_at' => now()->subDay(),
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id, 'order_number' => 'PURCHASE-'.uniqid(), 'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => 100000, 'total_amount' => 100000, 'paid_at' => now(),
            'metadata' => ['purchase_notification_eligible' => true, 'private' => 'PRIVATE-METADATA'],
        ]);
        $this->item($order, $product);

        return $order;
    }

    private function item(Order $order, Product $product): OrderItem
    {
        return OrderItem::query()->create([
            'order_id' => $order->id, 'product_id' => $product->id, 'store_id' => $product->store_id,
            'purchasable_type' => 'product_sale_item', 'purchasable_id' => 1,
            'quantity' => 1, 'unit_price' => 100000, 'subtotal_amount' => 100000,
            'total_amount' => 100000, 'final_total_amount' => 100000,
        ]);
    }
}
