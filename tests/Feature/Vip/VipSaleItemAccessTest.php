<?php

namespace Tests\Feature\Vip;

use App\Application\Catalog\Actions\ProductInformation\SyncProductSaleItemsAction;
use App\Application\Commerce\Actions\Checkout\CompleteOrderAfterSuccessfulPaymentAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Application\Identity\Actions\HydrateAccountUserAction;
use App\Application\Vip\Actions\GenerateVipSaleItemDownloadUrlAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use App\Livewire\Front\Catalog\ProductBuyBox;
use App\Livewire\Front\Account\MyDownloadsSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class VipSaleItemAccessTest extends TestCase
{
    use RefreshDatabase;

    private function saleItem(bool $vipAccessEnabled = true): array
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
        $saleItem = ProductSaleItem::query()->create([
            'product_id' => $productId, 'sku' => 'VIP-FILE', 'base_price' => 50000,
            'status' => 'available', 'vip_access_enabled' => $vipAccessEnabled,
        ]);
        $file = ProductSaleItemFile::query()->create([
            'product_sale_item_id' => $saleItem->id,
            'title' => 'فایل اصلی',
            'url' => 'https://downloads.example.com/file.zip',
            'is_active' => true,
        ]);

        return [Product::query()->findOrFail($productId), $saleItem, $file];
    }

    private function buyVip(User $user): VipSubscription
    {
        $plan = VipPlan::query()->create([
            'code' => 'vip-month-'.$user->id, 'title' => 'پلن ماهانه', 'duration_days' => 30,
            'price_toman' => 100000, 'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('vip.cart.add', $plan));
        $order = app(CreateOrderFromCartAction::class)->execute(Cart::query()->active()->firstOrFail());
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($order);

        return VipSubscription::query()->where('order_id', $order->id)->firstOrFail();
    }

    private function claimVipSaleItem(User $user, Product $product, ProductSaleItem $saleItem): void
    {
        Livewire::actingAs($user)->test(ProductBuyBox::class, ['product' => $product])
            ->call('claimVipSaleItem', $saleItem->id)
            ->assertRedirect(route('account.downloads', ['vipItem' => $saleItem->id]));
    }

    private function vipDownloadUrl(ProductSaleItem $saleItem, ProductSaleItemFile $file): string
    {
        return app(GenerateVipSaleItemDownloadUrlAction::class)->execute($saleItem, $file);
    }

    public function test_vip_download_rejects_unsigned_modified_and_expired_links(): void
    {
        Http::fake(['downloads.example.com/*' => Http::response('file bytes')]);
        [$product, $saleItem, $file] = $this->saleItem();
        $user = User::factory()->create();
        $this->buyVip($user);
        $this->claimVipSaleItem($user, $product, $saleItem);

        $signedUrl = $this->vipDownloadUrl($saleItem, $file);
        $this->get(route('downloads.vip-sale-item.file', [$saleItem, $file]))->assertForbidden();
        $this->get(str_replace('&signature=', '&modified=1&signature=', $signedUrl))->assertForbidden();

        $this->travel(11)->minutes();
        $this->get($signedUrl)->assertForbidden();
        $this->assertDatabaseCount('vip_sale_item_file_downloads', 0);
        Http::assertNothingSent();
    }

    public function test_vip_download_requires_a_current_subscription_and_never_creates_purchase_access(): void
    {
        Http::fake(['downloads.example.com/*' => Http::response('file bytes', 200, ['Content-Type' => 'application/zip'])]);
        [$product, $saleItem, $file] = $this->saleItem();
        $url = $this->vipDownloadUrl($saleItem, $file);

        $this->get($url)->assertRedirect(route('login'));
        $user = User::factory()->create();
        $this->actingAs($user)->get($url)->assertForbidden();

        $subscription = $this->buyVip($user);
        $this->get($url)->assertForbidden();
        $this->claimVipSaleItem($user, $product, $saleItem);
        $this->get($url)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/zip')
            ->assertHeaderMissing('Location')
            ->assertStreamedContent('file bytes');
        $this->assertDatabaseCount('purchase_accesses', 0);
        $this->assertDatabaseHas('vip_sale_item_claims', [
            'user_id' => $user->id,
            'product_sale_item_id' => $saleItem->id,
        ]);

        $subscription->update(['expires_at' => now()->subSecond()]);
        $this->get($url)->assertForbidden();

        $subscription->update(['expires_at' => now()->addDay(), 'revoked_at' => now()]);
        $this->get($url)->assertForbidden();

        $subscription->update(['revoked_at' => null, 'starts_at' => now()->addHour()]);
        $this->get($url)->assertForbidden();
    }

    public function test_vip_download_forwards_a_single_byte_range_without_exposing_the_source_url(): void
    {
        Http::fake(['downloads.example.com/*' => Http::response('part', 206, [
            'Content-Type' => 'application/zip',
            'Content-Range' => 'bytes 0-3/100',
            'Content-Length' => '4',
        ])]);
        [$product, $saleItem, $file] = $this->saleItem();
        $user = User::factory()->create();
        $this->buyVip($user);
        $this->claimVipSaleItem($user, $product, $saleItem);
        $url = $this->vipDownloadUrl($saleItem, $file);

        $this->withHeader('Range', 'bytes=0-3')->get($url)
            ->assertStatus(206)
            ->assertHeader('Content-Range', 'bytes 0-3/100')
            ->assertHeaderMissing('Location')
            ->assertStreamedContent('part');
        Http::assertSent(fn ($request) => $request->hasHeader('Range', 'bytes=0-3'));

        $this->withHeader('Range', 'bytes=0-3,10-12')->get($url)->assertStatus(416);
        $this->assertDatabaseHas('vip_sale_item_file_downloads', [
            'product_sale_item_file_id' => $file->id,
            'downloads_count' => 1,
        ]);
    }

    public function test_vip_download_limit_is_counted_per_file_and_enforced_on_direct_requests(): void
    {
        Http::fake(['downloads.example.com/*' => Http::response('file bytes', 200)]);
        [$product, $saleItem, $file] = $this->saleItem();
        $saleItem->update(['download_limit' => 2]);
        $otherFile = ProductSaleItemFile::query()->create([
            'product_sale_item_id' => $saleItem->id,
            'title' => 'فایل دوم',
            'url' => 'https://downloads.example.com/second.zip',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $this->buyVip($user);
        $this->claimVipSaleItem($user, $product, $saleItem);
        $url = $this->vipDownloadUrl($saleItem, $file);

        $this->head($url)->assertOk();
        $this->assertDatabaseCount('vip_sale_item_file_downloads', 0);

        $this->get($url)->assertOk();
        Livewire::actingAs($user)->test(MyDownloadsSection::class)
            ->assertSee('1 دانلود باقی‌مانده');
        $this->get($url)->assertOk();
        $this->get($url)->assertForbidden();
        $this->assertDatabaseHas('vip_sale_item_file_downloads', [
            'product_sale_item_file_id' => $file->id,
            'downloads_count' => 2,
        ]);
        Livewire::actingAs($user)->test(MyDownloadsSection::class)
            ->assertSee('0 دانلود باقی‌مانده')
            ->assertSee('سهمیه پایان یافته');

        $this->get($this->vipDownloadUrl($saleItem, $otherFile))->assertOk();
        $this->assertDatabaseHas('vip_sale_item_file_downloads', [
            'product_sale_item_file_id' => $otherFile->id,
            'downloads_count' => 1,
        ]);

        $saleItem->update(['download_limit' => 0]);
        $this->get($this->vipDownloadUrl($saleItem, $otherFile))->assertForbidden();
    }

    public function test_claim_is_per_user_idempotent_and_requires_current_eligibility(): void
    {
        [$product, $saleItem, $file] = $this->saleItem();
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ProductBuyBox::class, ['product' => $product])
            ->call('claimVipSaleItem', $saleItem->id)
            ->assertForbidden();
        $this->assertDatabaseCount('vip_sale_item_claims', 0);

        $this->buyVip($user);
        $this->claimVipSaleItem($user, $product, $saleItem);
        $this->claimVipSaleItem($user, $product, $saleItem);
        $this->assertDatabaseCount('vip_sale_item_claims', 1);

        $otherUser = User::factory()->create();
        $this->buyVip($otherUser);
        $this->actingAs($otherUser)
            ->get($this->vipDownloadUrl($saleItem, $file))
            ->assertForbidden();

        $saleItem->update(['vip_access_enabled' => false]);
        Livewire::actingAs($otherUser)->test(ProductBuyBox::class, ['product' => $product])
            ->call('claimVipSaleItem', $saleItem->id)
            ->assertNotFound();
        $this->assertDatabaseCount('vip_sale_item_claims', 1);
    }

    public function test_vip_download_checks_item_product_and_file_on_every_request(): void
    {
        Http::fake(['downloads.example.com/*' => Http::response('file bytes')]);
        [$product, $saleItem, $file] = $this->saleItem();
        $user = User::factory()->create();
        $this->buyVip($user);
        $this->claimVipSaleItem($user, $product, $saleItem);
        $url = $this->vipDownloadUrl($saleItem, $file);

        $this->get($url)->assertOk();

        $saleItem->update(['vip_access_enabled' => false]);
        $this->get($url)->assertForbidden();
        $saleItem->update(['vip_access_enabled' => true, 'status' => 'unavailable']);
        $this->get($url)->assertForbidden();
        $saleItem->update(['status' => 'available']);

        $file->update(['is_active' => false]);
        $this->get($url)->assertForbidden();
        $file->update(['is_active' => true]);

        $otherFile = ProductSaleItemFile::query()->create([
            'product_sale_item_id' => ProductSaleItem::query()->create([
                'product_id' => $product->id, 'sku' => 'OTHER', 'base_price' => 1000,
                'status' => 'available', 'vip_access_enabled' => true,
            ])->id,
            'url' => 'https://downloads.example.com/other.zip', 'is_active' => true,
        ]);
        $this->get($this->vipDownloadUrl($saleItem, $otherFile))->assertForbidden();

        $product->update(['status' => 'inactive']);
        $this->get($url)->assertForbidden();
        $product->update(['status' => 'active', 'published_at' => now()->addDay()]);
        $this->get($url)->assertForbidden();
    }

    public function test_launcher_only_sale_item_cannot_expose_its_files_through_vip(): void
    {
        [$product, $saleItem, $file] = $this->saleItem();
        $user = User::factory()->create();
        $this->buyVip($user);

        $saleItem->update(['launcher_enabled' => true]);

        Livewire::actingAs($user)->test(ProductBuyBox::class, ['product' => $product])
            ->assertSet('saleItems.0.vip_access_enabled', false);
        Livewire::actingAs($user)->test(ProductBuyBox::class, ['product' => $product])
            ->call('claimVipSaleItem', $saleItem->id)
            ->assertNotFound();
        $this->actingAs($user)
            ->get($this->vipDownloadUrl($saleItem, $file))
            ->assertForbidden();
    }

    public function test_buy_box_links_to_plans_or_account_downloads_without_exposing_file_links(): void
    {
        [$product, $saleItem] = $this->saleItem();

        Livewire::test(ProductBuyBox::class, ['product' => $product])
            ->assertSee('با اشتراک VIP')
            ->assertSee('مشاهده پلن‌های VIP')
            ->assertDontSee('دانلود فایل');

        $this->buyVip(User::factory()->create());
        Livewire::test(ProductBuyBox::class, ['product' => $product])
            ->assertSee('افزودن به دانلودها')
            ->assertDontSee('vip_files')
            ->assertDontSee('دانلود فایل')
            ->assertDontSee('مشاهده پلن‌های VIP');

        $user = auth()->user();
        $this->claimVipSaleItem($user, $product, $saleItem);
        Livewire::actingAs($user)->test(ProductBuyBox::class, ['product' => $product])
            ->assertSee('مشاهده دانلودها');
    }

    public function test_vip_items_appear_only_while_subscription_is_active_in_account_downloads(): void
    {
        [, $saleItem, $file] = $this->saleItem();
        $user = User::factory()->create();
        $subscription = $this->buyVip($user);

        Livewire::actingAs($user)->test(MyDownloadsSection::class)
            ->assertDontSee('دانلودهای افزوده‌شده با VIP')
            ->assertDontSee('فایل اصلی');
        $this->assertSame(0, app(HydrateAccountUserAction::class)->execute($user)->purchase_accesses_count);

        $this->claimVipSaleItem($user, Product::query()->findOrFail($saleItem->product_id), $saleItem);
        Livewire::actingAs($user)->test(MyDownloadsSection::class)
            ->assertSee('دانلودهای افزوده‌شده با VIP')
            ->assertSee('Test Product')
            ->assertSee('فایل اصلی')
            ->assertDontSee('خریدهای من')
            ->assertSee(route('downloads.vip-sale-item.file', [$saleItem, $file]))
            ->assertSee('expires=', escape: false)
            ->assertSee('signature=', escape: false);
        $this->assertSame(1, app(HydrateAccountUserAction::class)->execute($user)->purchase_accesses_count);

        $subscription->update(['expires_at' => now()->subSecond()]);
        Livewire::actingAs($user)->test(MyDownloadsSection::class)
            ->assertSee('دانلودهای افزوده‌شده با VIP')
            ->assertSee('در حال حاضر غیرفعال')
            ->assertDontSee('فایل اصلی');
        $this->assertSame(0, app(HydrateAccountUserAction::class)->execute($user)->purchase_accesses_count);
        $this->get($this->vipDownloadUrl($saleItem, $file))->assertForbidden();

        $subscription->update(['expires_at' => now()->addDay()]);
        Livewire::actingAs($user)->test(MyDownloadsSection::class)
            ->assertSee('فعال با اشتراک فعلی')
            ->assertSee('فایل اصلی');
        $this->assertSame(1, app(HydrateAccountUserAction::class)->execute($user)->purchase_accesses_count);
    }

    public function test_product_link_can_focus_its_vip_item_in_account_downloads(): void
    {
        [$product, $saleItem] = $this->saleItem();
        $product->installationSteps()->create(['body' => 'راهنمای نصب آزمایشی', 'sort_order' => 0]);
        $otherItem = ProductSaleItem::query()->create([
            'product_id' => $product->id, 'sku' => 'VIP-OTHER',
            'title' => 'نسخه دیگر', 'base_price' => 40000,
            'status' => 'available', 'vip_access_enabled' => true,
        ]);
        ProductSaleItemFile::query()->create([
            'product_sale_item_id' => $otherItem->id,
            'title' => 'فایل نسخه دیگر',
            'url' => 'https://downloads.example.com/other.zip',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $this->buyVip($user);
        $this->claimVipSaleItem($user, $product, $saleItem);
        $this->claimVipSaleItem($user, $product, $otherItem);

        Livewire::actingAs($user)->test(MyDownloadsSection::class)
            ->set('vipItem', $saleItem->id)
            ->assertSee('فایل اصلی')
            ->assertSee('فایل نسخه دیگر')
            ->assertSee('id="vip-claim-'.$user->vipSaleItemClaims()->where('product_sale_item_id', $saleItem->id)->value('id').'"', escape: false)
            ->assertSee('راهنمای نصب آزمایشی')
            ->assertDontSee('نمایش همه دانلودها');

        $this->actingAs($user)
            ->get(route('account.downloads', ['vipItem' => $saleItem->id]))
            ->assertOk()
            ->assertSee('فایل اصلی')
            ->assertSee('فایل نسخه دیگر')
            ->assertSee('open = true;')
            ->assertSee('scrollIntoView');
    }

    public function test_vip_download_list_reacts_to_item_file_and_product_availability(): void
    {
        [$product, $saleItem, $file] = $this->saleItem();
        $user = User::factory()->create();
        $this->buyVip($user);
        $this->claimVipSaleItem($user, $product, $saleItem);

        $file->update(['is_active' => false]);
        Livewire::actingAs($user)->test(MyDownloadsSection::class)->assertSee('Test Product')->assertDontSee('فایل اصلی');

        $file->update(['is_active' => true]);
        $saleItem->update(['vip_access_enabled' => false]);
        Livewire::actingAs($user)->test(MyDownloadsSection::class)->assertSee('Test Product')->assertSee('در حال حاضر غیرفعال');

        $saleItem->update(['vip_access_enabled' => true]);
        $product->update(['published_at' => now()->addDay()]);
        Livewire::actingAs($user)->test(MyDownloadsSection::class)->assertSee('Test Product')->assertSee('در حال حاضر غیرفعال');
    }

    public function test_admin_sale_item_sync_can_enable_and_disable_vip_access(): void
    {
        [$product, $saleItem, $file] = $this->saleItem(false);
        $sync = app(SyncProductSaleItemsAction::class);
        $payload = [
            'sale_items' => [[
                'id' => $saleItem->id,
                'sku' => $saleItem->sku,
                'base_price' => $saleItem->base_price,
                'status' => 'available',
                'files' => [['id' => $file->id, 'title' => $file->title, 'url' => $file->url]],
                'vip_access_enabled' => true,
            ]],
        ];

        $sync->execute($product, $payload, []);
        $this->assertTrue($saleItem->fresh()->vip_access_enabled);

        $payload['sale_items'][0]['vip_access_enabled'] = false;
        $sync->execute($product, $payload, []);
        $this->assertFalse($saleItem->fresh()->vip_access_enabled);
    }
}
