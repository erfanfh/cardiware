<?php

namespace Tests\Feature\Marketplace;

use App\Application\Marketplace\Actions\GetSellerProductSalesAction;
use App\Application\Marketplace\Actions\GetSellerStoreReportAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\SellerPermissionEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Support\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerStorePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_stores_keep_access_and_unknown_permissions_fail_closed(): void
    {
        $store = new Store;
        foreach (SellerPermissionEnum::cases() as $permission) {
            self::assertTrue($store->sellerCan($permission));
        }
        self::assertFalse($store->sellerCan('unknown'));
        $store->seller_permissions = ['view_revenue' => null];
        self::assertFalse($store->sellerCan('view_revenue'));
        self::assertFalse($store->sellerCan('manage_withdrawals'));
    }

    public function test_admin_can_create_update_and_preserve_per_store_permissions(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.stores.store'), [
            'title' => 'Restricted', 'slug' => 'restricted', 'status' => 'active',
            'seller_permissions' => ['view_revenue' => '0', 'view_sales_count' => '0'],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $store = Store::where('slug', 'restricted')->firstOrFail();
        self::assertSame(['view_revenue' => false, 'view_sales_count' => false], $store->seller_permissions);
        $this->get(route('admin.stores.edit', $store))->assertOk()
            ->assertSee('دسترسی‌های پنل فروشندگان')
            ->assertSee('seller_permissions[view_buyer_info]', false);
        $this->get(route('admin.stores.create'))->assertOk()->assertSee('seller_permissions[access_panel]', false);

        $this->put(route('admin.stores.update', $store), [
            'title' => $store->title, 'slug' => $store->slug, 'status' => 'active',
            'seller_permissions' => ['view_sales_count' => '1'],
        ])->assertSessionHasNoErrors()->assertRedirect();
        self::assertTrue($store->fresh()->sellerCan('view_sales_count'));
        self::assertFalse($store->fresh()->sellerCan('view_revenue'));

        $this->put(route('admin.stores.update', $store), [
            'title' => $store->title, 'slug' => $store->slug, 'status' => 'active',
        ])->assertSessionHasNoErrors()->assertRedirect();
        self::assertFalse($store->fresh()->sellerCan('view_revenue'));
    }

    public function test_admin_rejects_unknown_keys_non_boolean_and_null_permissions(): void
    {
        $this->actingAs($this->admin());
        foreach ([['unknown' => true], ['view_revenue' => 'invalid'], ['view_revenue' => null]] as $settings) {
            $this->post(route('admin.stores.store'), [
                'title' => 'Invalid', 'status' => 'active', 'seller_permissions' => $settings,
            ])->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('stores', 0);
    }

    public function test_hiding_sales_counts_removes_all_counts_and_blocks_direct_sales_details(): void
    {
        [$seller, $store, $product] = $this->fixture(['view_sales_count' => false]);
        $report = app(GetSellerStoreReportAction::class)->execute($store, 30);
        self::assertNull($report['summary']['orders']);
        self::assertNull($report['summary']['units']);
        self::assertNull($report['lifetime']['orders']);
        self::assertFalse(property_exists($report['top_products']->first(), 'units_count'));
        self::assertSame(654321, $report['summary']['seller']);

        $this->actingAs($seller)->get(route('seller.stores.dashboard', $store))->assertOk()
            ->assertDontSee('تعداد فروش محصول')->assertDontSee('سفارش یکتا')
            ->assertSee('654,321 تومان');
        $this->get(route('seller.stores.products', $store))->assertOk()
            ->assertDontSee('تعداد فروش:')->assertDontSee('افزونه فروخته‌شده:')->assertDontSee('جزئیات فروش')
            ->assertViewHas('sales', fn ($sales) => ! property_exists($sales->first(), 'orders_count'));
        $this->get(route('seller.stores.products.sales', [$store, $product->id]))->assertForbidden();
    }

    public function test_hiding_revenue_redacts_all_reports_and_disables_withdrawals(): void
    {
        [$seller, $store, $product] = $this->fixture(['view_revenue' => false]);
        $dashboard = app(GetSellerStoreReportAction::class)->execute($store, 30);
        self::assertNull($dashboard['summary']['seller']);
        self::assertNull($dashboard['summary']['gross']);
        self::assertNull($dashboard['summary']['platform']);
        self::assertNull($dashboard['lifetime']['seller']);
        self::assertNull($dashboard['chart']);
        self::assertTrue($dashboard['top_products']->isEmpty());
        $report = app(GetSellerProductSalesAction::class)->execute($store, $product, 'all');
        foreach (['net_amount', 'seller_amount', 'platform_amount', 'product_net_amount', 'addon_seller_amount'] as $field) {
            self::assertNull($report['summary'][$field]);
        }
        self::assertNull($report['sale_items']->first()['seller_amount']);
        self::assertArrayNotHasKey('final_total_amount', $report['recent_items']->first()->getAttributes());
        self::assertArrayNotHasKey('seller_commission_amount', $report['recent_items']->first()->getAttributes());

        $this->actingAs($seller)->get(route('seller.stores.dashboard', $store))->assertOk()
            ->assertDontSee('654,321')->assertDontSee('987,654')->assertDontSee('data-chart', false)
            ->assertDontSee('ماندهٔ قابل درخواست وجه')->assertViewHas('withdrawalBalance', null);
        $this->get(route('seller.stores.products', $store))->assertOk()
            ->assertDontSee('654,321')->assertViewHas('sales', fn ($sales) => ! property_exists($sales->first(), 'seller_amount'));
        $this->get(route('seller.stores.products.sales', [$store, $product->id]))->assertOk()
            ->assertDontSee('654,321')->assertDontSee('987,654')->assertDontSee('درآمد فروشگاه')
            ->assertSee('PERMISSION-ORDER');
        $this->get(route('seller.stores.withdrawals', $store))->assertForbidden();
        $this->post(route('seller.stores.withdrawals.store', $store), [])->assertForbidden();
        $this->assertDatabaseCount('store_withdrawals', 0);
    }

    public function test_hiding_buyer_info_keeps_order_numbers_but_blocks_customer_content_and_attachments(): void
    {
        Storage::fake('local');
        [$seller, $store, $product, $ticket] = $this->fixture(['view_buyer_info' => false]);
        $message = $ticket->messages()->create([
            'user_id' => $ticket->user_id, 'sender_type' => 'user', 'body' => 'Private customer message',
        ]);
        $attachment = $message->attachments()->create([
            'uploaded_by_user_id' => $ticket->user_id, 'original_name' => 'private.webp',
        ]);
        $path = 'support/ticket-images/2026/10/123e4567-e89b-12d3-a456-426614174000/thumbnail.webp';
        Storage::disk('local')->put($path, 'image bytes');
        $attachment->variants()->create([
            'variant' => 'thumbnail', 'mime_type' => 'image/webp', 'path' => $path,
            'byte_size' => 11, 'width' => 1, 'height' => 1, 'checksum' => str_repeat('a', 64),
        ]);
        $url = route('support.ticket-attachments.show', ['ticketMessageAttachment' => $attachment, 'variant' => 'thumbnail']);

        $this->actingAs($seller)->get(route('seller.stores.products.sales', [$store, $product->id]))->assertOk()
            ->assertSee('PERMISSION-ORDER')->assertDontSee('private-buyer@example.test')
            ->assertDontSee('Private customer message')->assertDontSee('PRIVATE-SNAPSHOT');
        $report = app(GetSellerProductSalesAction::class)->execute($store, $product, 'all');
        $item = $report['recent_items']->first();
        self::assertArrayNotHasKey('snapshot', $item->getAttributes());
        self::assertArrayNotHasKey('metadata', $item->getAttributes());
        self::assertArrayNotHasKey('user_id', $item->order->getAttributes());
        self::assertFalse($item->order->relationLoaded('user'));
        $this->get(route('seller.stores.dashboard', $store))->assertOk()
            ->assertDontSee('Private ticket')->assertDontSee('تیکت‌های نیازمند پاسخ')
            ->assertViewHas('recentTickets', fn ($tickets) => $tickets->isEmpty())
            ->assertViewHas('commentsCount', null);
        $this->get(route('seller.stores.comments', $store))->assertForbidden();
        $this->get(route('seller.stores.tickets', $store))->assertForbidden();
        $this->get(route('seller.stores.tickets.show', [$store, $ticket]))->assertForbidden();
        $this->post(route('seller.stores.tickets.reply', [$store, $ticket]), ['message' => 'Forbidden reply'])->assertForbidden();
        $this->get($url)->assertNotFound();

        $store->update(['seller_permissions' => ['view_tickets' => false]]);
        $this->get($url)->assertNotFound();
        $store->update(['seller_permissions' => []]);
        $this->get($url)->assertOk();
        $store->update(['seller_permissions' => ['access_panel' => false]]);
        $this->get($url)->assertNotFound();
        // Their own customer access does not depend on their seller permissions.
        $this->actingAs($ticket->user)->get($url)->assertOk();
    }

    public function test_tickets_can_be_read_only_and_revoked_immediately(): void
    {
        [$seller, $store, $product, $ticket] = $this->fixture(['reply_tickets' => false]);
        $this->actingAs($seller)->get(route('seller.stores.tickets.show', [$store, $ticket]))->assertOk()
            ->assertSee('فقط برای مشاهده')->assertDontSee('name="message"', false);
        $this->post(route('seller.stores.tickets.reply', [$store, $ticket]), ['message' => 'Forbidden reply'])->assertForbidden();
        self::assertSame(0, $ticket->messages()->count());
        $store->update(['seller_permissions' => ['view_tickets' => false]]);
        $this->get(route('seller.stores.tickets.show', [$store, $ticket]))->assertForbidden();
    }

    public function test_additional_sections_can_be_disabled_independently(): void
    {
        [$seller, $store, $product, $ticket] = $this->fixture([
            'view_sales_details' => false, 'view_comments' => false, 'manage_withdrawals' => false,
        ]);
        $this->actingAs($seller)->get(route('seller.stores.dashboard', $store))->assertOk()->assertSee('654,321 تومان');
        $this->get(route('seller.stores.products.sales', [$store, $product->id]))->assertForbidden();
        $this->get(route('seller.stores.comments', $store))->assertForbidden();
        $this->get(route('seller.stores.withdrawals', $store))->assertForbidden();
        $this->post(route('seller.stores.withdrawals.store', $store), [])->assertForbidden();
        $this->get(route('seller.stores.tickets.show', [$store, $ticket]))->assertOk();
    }

    public function test_commission_details_can_be_hidden_without_hiding_revenue(): void
    {
        [$seller, $store, $product] = $this->fixture(['view_commission' => false]);
        $report = app(GetSellerProductSalesAction::class)->execute($store, $product, 'all');
        self::assertSame(654321, $report['summary']['seller_amount']);
        self::assertNull($report['summary']['platform_amount']);
        self::assertNull($report['sale_items']->first()['effective_seller_percent']);
        self::assertArrayNotHasKey('seller_commission_basis_points', $report['recent_items']->first()->getAttributes());
        $this->actingAs($seller)->get(route('seller.stores.products.sales', [$store, $product->id]))->assertOk()
            ->assertSee('654,321 تومان')->assertDontSee('نرخ مؤثر')->assertDontSee('درصد فروشگاه')->assertDontSee('سهم پلتفرم');
        $this->get(route('seller.stores.products', $store))->assertOk()->assertDontSee('کمیسیون فروشگاه:')
            ->assertViewHas('products', fn ($products) => ! array_key_exists('seller_commission_basis_points', $products->first()->getAttributes()));
        $this->get(route('seller.stores.dashboard', $store))->assertOk()->assertDontSee('سهم پلتفرم');
    }

    public function test_panel_can_be_revoked_for_all_assigned_sellers_without_affecting_other_stores(): void
    {
        [$seller, $store, $product] = $this->fixture(['access_panel' => false]);
        $other = Store::create(['title' => 'Allowed store', 'slug' => 'allowed', 'status' => 'active']);
        $other->sellers()->attach($seller);
        $secondSeller = User::factory()->create();
        $store->sellers()->attach($secondSeller);
        foreach ([$seller, $secondSeller] as $assigned) {
            $this->actingAs($assigned)->get(route('seller.stores.dashboard', $store))->assertForbidden();
            $this->get(route('seller.stores.products', $store))->assertForbidden();
        }
        $this->actingAs($seller)->get(route('seller.dashboard'))->assertOk()
            ->assertSee('Allowed store')->assertDontSee('Restricted store');
        $this->get(route('seller.stores.dashboard', $other))->assertOk();
        $store->update(['seller_permissions' => ['view_revenue' => false, 'view_sales_count' => false]]);
        $this->get(route('seller.stores.dashboard', $store))->assertOk()->assertDontSee('data-chart', false);
        $this->get(route('seller.stores.products', $store))->assertOk()->assertViewHas('sales', fn ($sales) => $sales->isEmpty());
    }

    public function test_seller_cannot_change_permissions_through_seller_endpoints(): void
    {
        [$seller, $store] = $this->fixture(['view_revenue' => false]);
        $this->actingAs($seller)->put(route('admin.stores.update', $store), [
            'title' => $store->title, 'status' => 'active', 'seller_permissions' => ['view_revenue' => true],
        ])->assertRedirect(route('account.dashboard'));
        $this->get(route('seller.stores.dashboard', [$store, 'seller_permissions' => ['view_revenue' => true]]))
            ->assertOk()->assertDontSee('654,321');
        self::assertFalse($store->fresh()->sellerCan('view_revenue'));
    }

    private function admin(): User
    {
        return User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
    }

    private function fixture(array $permissions): array
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create(['email' => 'private-buyer@example.test']);
        $store = Store::create([
            'title' => 'Restricted store', 'slug' => 'permissions', 'status' => 'active', 'seller_permissions' => $permissions,
        ]);
        $store->sellers()->attach($seller);
        $product = Product::create([
            'store_id' => $store->id, 'title' => 'Permission product', 'slug' => 'permission-product',
            'type' => 'digital', 'structure' => 'simple', 'status' => 'active', 'published_at' => now(),
        ]);
        $order = Order::create([
            'user_id' => $buyer->id, 'order_number' => 'PERMISSION-ORDER', 'status' => 'paid',
            'subtotal_amount' => 987654, 'total_amount' => 987654, 'paid_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'store_id' => $store->id, 'product_id' => $product->id,
            'purchasable_type' => 'product_sale_item', 'purchasable_id' => 1, 'quantity' => 37,
            'unit_price' => 987654, 'subtotal_amount' => 987654, 'total_amount' => 987654,
            'final_total_amount' => 987654, 'seller_commission_amount' => 654321,
            'platform_commission_amount' => 333333, 'seller_commission_basis_points' => 6625,
            'snapshot' => ['title' => 'Historical title', 'customer_email' => 'PRIVATE-SNAPSHOT'],
            'metadata' => ['customer_email' => $buyer->email],
        ]);
        $ticket = Ticket::create([
            'user_id' => $buyer->id, 'product_id' => $product->id, 'number' => random_int(100000, 999999),
            'title' => 'Private ticket', 'status' => 'open', 'last_message_at' => now(),
        ]);

        return [$seller, $store, $product, $ticket];
    }
}
