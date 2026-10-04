<?php

namespace Tests\Feature\Marketplace;

use App\Application\Marketplace\Actions\GetSellerStoreReportAction;
use App\Application\Marketplace\Actions\GetSellerProductSalesAction;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Models\Ticket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorePanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_sales_show_paid_product_and_addon_details_from_commission_snapshots(): void
    {
        $seller = User::factory()->create();
        $store = $this->store('my-store');
        $store->sellers()->attach($seller);
        $product = $this->product($store, 'My product', ProductStatusEnum::INACTIVE);

        $paid = $this->order($product, OrderStatusEnum::PAID, 100, 30, 'product_sale_item');
        $this->orderItem($paid, $product, 50, 20, 'product_addon');
        $paid->items()->where('purchasable_type', 'product_addon')->firstOrFail()
            ->update(['snapshot' => ['product_addon_title' => 'Historical addon']]);
        $this->order($product, OrderStatusEnum::REFUNDED, 200, 100, 'product_sale_item');
        $this->order($product, OrderStatusEnum::PENDING_PAYMENT, 300, 150, 'product_sale_item');

        $product->update(['seller_commission_basis_points' => 9000]);
        $report = app(GetSellerProductSalesAction::class)->execute($store, $product, 'all');
        $this->assertSame(1, $report['summary']['orders_count']);
        $this->assertSame(150, $report['summary']['net_amount']);
        $this->assertSame(50, $report['summary']['seller_amount']);
        $this->assertSame(100, $report['summary']['platform_amount']);
        $this->assertSame(1, $report['summary']['product_units']);
        $this->assertSame(1, $report['summary']['addon_units']);
        $this->assertSame(30, $report['summary']['product_seller_amount']);
        $this->assertSame(20, $report['summary']['addon_seller_amount']);
        $this->assertSame('Historical addon', $report['addons']->first()['title']);
        $this->assertSame(40.0, $report['addons']->first()['effective_seller_percent']);
        $this->assertSame(2, $report['recent_items']->total());

        $this->actingAs($seller)->get(route('seller.stores.products', $store))
            ->assertOk()->assertSee('جزئیات فروش');
        $this->get(route('seller.stores.products.sales', [$store, $product->id]))
            ->assertOk()->assertSee('Historical addon')->assertSee('50 تومان');
    }

    public function test_product_sales_are_scoped_to_store_and_date_range(): void
    {
        $seller = User::factory()->create();
        $own = $this->store('own');
        $other = $this->store('other');
        $own->sellers()->attach($seller);
        $ownProduct = $this->product($own, 'Own');
        $otherProduct = $this->product($other, 'Other');
        $this->order($ownProduct, OrderStatusEnum::PAID, 100, 40, 'product_sale_item');
        $old = $this->order($ownProduct, OrderStatusEnum::PAID, 200, 80, 'product_sale_item');
        $old->update(['paid_at' => now()->subDays(120)]);
        $this->order($otherProduct, OrderStatusEnum::PAID, 900, 450, 'product_sale_item');

        $report = app(GetSellerProductSalesAction::class)->execute($own, $ownProduct, '30');
        $this->assertSame(100, $report['summary']['net_amount']);
        $this->assertSame(40, $report['summary']['seller_amount']);
        $this->assertSame(1, $report['recent_items']->total());
        $this->assertSame(300, app(GetSellerProductSalesAction::class)->execute($own, $ownProduct, 'all')['summary']['net_amount']);

        $this->actingAs($seller)
            ->get(route('seller.stores.products.sales', [$own, $ownProduct->id, 'period' => '30']))
            ->assertOk()
            ->assertViewHas('report', fn (array $value) => $value['summary']['net_amount'] === 100);

        $emptyProduct = $this->product($own, 'Empty');
        $this->get(route('seller.stores.products.sales', [$own, $emptyProduct->id]))
            ->assertOk()
            ->assertSee('هنوز فروشی برای افزونه‌های این محصول ثبت نشده است.');

        $this->get(route('seller.stores.products.sales', [$own, $otherProduct->id]))
            ->assertNotFound();
        $this->get(route('seller.stores.products.sales', [$other, $otherProduct->id]))
            ->assertForbidden();
    }

    public function test_report_uses_paid_order_snapshots_including_addons_and_excludes_refunds(): void
    {
        $seller = User::factory()->create();
        $store = $this->store('my-store');
        $store->sellers()->attach($seller);
        $product = $this->product($store, 'My product');

        $paid = $this->order($product, OrderStatusEnum::PAID, 100, 30, 'product_sale_item');
        $this->orderItem($paid, $product, 50, 20, 'product_addon');
        $this->order($product, OrderStatusEnum::REFUNDED, 200, 100, 'product_sale_item');
        $this->order($product, OrderStatusEnum::PENDING_PAYMENT, 300, 150, 'product_sale_item');

        $report = app(GetSellerStoreReportAction::class)->execute($store, 30);
        $this->assertSame(['orders' => 1, 'units' => 1, 'gross' => 150, 'seller' => 50, 'platform' => 100], $report['summary']);
        $this->assertSame(50, (int) $report['top_products']->first()->seller_amount);
        $this->assertSame(['orders' => 1, 'seller' => 50], $report['lifetime']);

        $product->update(['seller_commission_basis_points' => 9000]);
        $this->assertSame(50, app(GetSellerStoreReportAction::class)->execute($store, 30)['summary']['seller']);
        $this->actingAs($seller)->get(route('seller.stores.dashboard', $store))->assertOk()->assertSee('50 تومان');
    }

    public function test_products_comments_and_tickets_are_scoped_to_assigned_store(): void
    {
        $seller = User::factory()->create();
        $own = $this->store('own');
        $other = $this->store('other');
        $own->sellers()->attach($seller);
        $ownProduct = $this->product($own, 'Own product', ProductStatusEnum::INACTIVE);
        $otherProduct = $this->product($other, 'Other product');
        $this->comment($ownProduct, 'Own comment');
        $this->comment($otherProduct, 'Other comment');
        $ownTicket = $this->ticket($ownProduct, 'Own ticket');
        $otherTicket = $this->ticket($otherProduct, 'Other ticket');
        $answeredTicket = $this->ticket($ownProduct, 'Answered ticket');
        $answeredTicket->update(['status' => 'answered']);

        $this->actingAs($seller)->get(route('seller.stores.products', $own))
            ->assertOk()->assertSee('Own product')->assertDontSee('Other product')
            ->assertSee('aria-label="1 تیکت نیازمند پاسخ"', false);
        $this->get(route('seller.stores.comments', $own))
            ->assertOk()->assertSee('Own comment')->assertDontSee('Other comment');
        $this->get(route('seller.stores.tickets', $own))
            ->assertOk()->assertSee('Own ticket')->assertDontSee('Other ticket');
        $this->get(route('seller.stores.tickets.show', [$own, $ownTicket]))->assertOk();
        $this->get(route('seller.stores.tickets.show', [$own, $otherTicket]))->assertNotFound();
        $this->get(route('seller.stores.products', $other))->assertForbidden();
    }

    public function test_seller_can_reply_only_to_own_store_product_ticket(): void
    {
        $seller = User::factory()->create();
        $own = $this->store('own');
        $other = $this->store('other');
        $own->sellers()->attach($seller);
        $ownTicket = $this->ticket($this->product($own, 'Own'), 'Own ticket');
        $otherTicket = $this->ticket($this->product($other, 'Other'), 'Other ticket');

        $this->actingAs($seller)->post(route('seller.stores.tickets.reply', [$own, $otherTicket]), ['message' => 'A response'])
            ->assertNotFound();
        $this->post(route('seller.stores.tickets.reply', [$own, $ownTicket]), ['message' => 'Seller response'])
            ->assertRedirect();

        $message = $ownTicket->messages()->firstOrFail();
        $this->assertSame(TicketMessageSenderTypeEnum::SUPPORT, $message->sender_type);
        $this->assertSame('Seller response', $message->body);
        $this->assertSame($seller->id, $message->user_id);
        $this->assertSame('answered', $ownTicket->fresh()->status->value);
        $this->assertSame(0, $otherTicket->messages()->count());
        $this->get(route('seller.stores.tickets', $own))
            ->assertOk()
            ->assertSee('aria-label="0 تیکت نیازمند پاسخ"', false);

        $ownTicket->update(['status' => 'closed']);
        $this->post(route('seller.stores.tickets.reply', [$own, $ownTicket]), ['message' => 'Another response'])
            ->assertSessionHasErrors('message');
        $this->assertSame(1, $ownTicket->messages()->count());
    }

    public function test_ticket_images_are_only_available_while_the_seller_is_assigned_to_the_product_store(): void
    {
        Storage::fake('local');
        $seller = User::factory()->create();
        $store = $this->store('own');
        $store->sellers()->attach($seller);
        $ticket = $this->ticket($this->product($store, 'Own'), 'Own ticket');
        $message = $ticket->messages()->create([
            'user_id' => $ticket->user_id,
            'sender_type' => TicketMessageSenderTypeEnum::USER,
            'body' => 'Image attached',
        ]);
        $attachment = $message->attachments()->create([
            'uploaded_by_user_id' => $ticket->user_id, 'original_name' => 'image.webp',
        ]);
        $path = 'support/ticket-images/2026/10/123e4567-e89b-12d3-a456-426614174000/thumbnail.webp';
        Storage::disk('local')->put($path, 'image bytes');
        $attachment->variants()->create([
            'variant' => 'thumbnail', 'mime_type' => 'image/webp', 'path' => $path,
            'byte_size' => 11, 'width' => 1, 'height' => 1, 'checksum' => str_repeat('a', 64),
        ]);

        $url = route('support.ticket-attachments.show', ['ticketMessageAttachment' => $attachment, 'variant' => 'thumbnail']);
        $this->actingAs($seller)->get($url)->assertOk();
        $store->sellers()->detach($seller);
        $this->get($url)->assertNotFound();
    }

    public function test_seller_reply_uploads_and_displays_images_with_the_shared_ticket_composer(): void
    {
        Storage::fake('local');
        $seller = User::factory()->create();
        $store = $this->store('own');
        $store->sellers()->attach($seller);
        $ticket = $this->ticket($this->product($store, 'Own'), 'Own ticket');

        $this->actingAs($seller)
            ->get(route('seller.stores.tickets.show', [$store, $ticket]))
            ->assertOk()
            ->assertSee('افزودن تصویر به پیام');

        $this->post(route('seller.stores.tickets.reply', [$store, $ticket]), [
            'message' => 'Seller response with image',
            'attachments' => [UploadedFile::fake()->image('proof.png', 320, 240)],
        ])->assertRedirect(route('seller.stores.tickets.show', [$store, $ticket]));

        $message = $ticket->messages()->firstOrFail();
        $attachment = $message->attachments()->firstOrFail();
        $this->assertSame('proof.png', $attachment->original_name);
        $this->assertCount(5, $attachment->variants);

        $thumbnailUrl = route('support.ticket-attachments.show', [
            'ticketMessageAttachment' => $attachment,
            'variant' => 'thumbnail',
        ]);
        $originalUrl = route('support.ticket-attachments.show', [
            'ticketMessageAttachment' => $attachment,
            'variant' => 'original',
        ]);
        $this->get(route('seller.stores.tickets.show', [$store, $ticket]))
            ->assertOk()
            ->assertSee($thumbnailUrl)
            ->assertSee('href="'.$originalUrl.'" target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('ticket-image-open');
        $this->get($thumbnailUrl)->assertOk();
    }

    private function store(string $slug): Store
    {
        return Store::query()->create(['title' => $slug, 'slug' => $slug, 'status' => StoreStatusEnum::ACTIVE]);
    }

    private function product(Store $store, string $title, ProductStatusEnum $status = ProductStatusEnum::ACTIVE): Product
    {
        return Product::query()->create([
            'store_id' => $store->id, 'title' => $title, 'slug' => str()->slug($title),
            'type' => ProductTypeEnum::DIGITAL, 'structure' => ProductStructureEnum::SIMPLE,
            'status' => $status, 'published_at' => now(),
        ]);
    }

    private function order(Product $product, OrderStatusEnum $status, int $total, int $seller, string $type): Order
    {
        $order = Order::query()->create([
            'user_id' => User::factory()->create()->id, 'order_number' => 'SELL-'.uniqid(),
            'status' => $status, 'subtotal_amount' => $total, 'total_amount' => $total,
            'paid_at' => $status !== OrderStatusEnum::PENDING_PAYMENT ? now() : null,
        ]);
        $this->orderItem($order, $product, $total, $seller, $type);

        return $order;
    }

    private function orderItem(Order $order, Product $product, int $total, int $seller, string $type): void
    {
        OrderItem::query()->create([
            'order_id' => $order->id, 'purchasable_type' => $type, 'purchasable_id' => 1,
            'product_id' => $product->id, 'store_id' => $product->store_id, 'quantity' => 1,
            'unit_price' => $total, 'subtotal_amount' => $total, 'total_amount' => $total,
            'final_total_amount' => $total, 'seller_commission_amount' => $seller,
            'platform_commission_amount' => $total - $seller,
            'seller_commission_basis_points' => $total > 0 ? (int) round(10000 * $seller / $total) : 0,
            'platform_commission_basis_points' => $total > 0 ? 10000 - (int) round(10000 * $seller / $total) : 10000,
        ]);
    }

    private function comment(Product $product, string $content): void
    {
        Comment::query()->create([
            'commentable_type' => $product->getMorphClass(), 'commentable_id' => $product->id,
            'user_id' => User::factory()->create()->id, 'content' => $content,
            'status' => CommentStatusEnum::APPROVED,
        ]);
    }

    private function ticket(Product $product, string $title): Ticket
    {
        return Ticket::query()->create([
            'user_id' => User::factory()->create()->id, 'product_id' => $product->id,
            'number' => random_int(100000, 999999), 'title' => $title, 'status' => 'open',
            'last_message_at' => now(),
        ]);
    }
}
