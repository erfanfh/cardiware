<?php

namespace Tests\Feature\Vip;

use App\Application\Content\Services\ReservedSlugService;
use App\Application\Vip\Actions\GetVipProductListAction;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VipProductListTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_only_contains_published_products_with_an_available_vip_sale_item_once(): void
    {
        $included = $this->product('vip-product', 'active', now());
        $this->saleItem($included, true);
        $this->saleItem($included, true);
        $this->saleItem($included, false);

        $regular = $this->product('regular-product', 'active', now());
        $this->saleItem($regular, false);

        $unavailable = $this->product('unavailable-product', 'active', now());
        $this->saleItem($unavailable, true, 'unavailable');

        $withoutFile = $this->product('without-file-product', 'active', now());
        $this->saleItem($withoutFile, true, 'available', false);

        $launcherOnly = $this->product('launcher-product', 'active', now());
        $this->saleItem($launcherOnly, true, 'available', true, true);

        $future = $this->product('future-product', 'active', now()->addDay());
        $this->saleItem($future, true);

        $inactive = $this->product('inactive-product', 'inactive', now());
        $this->saleItem($inactive, true);

        $products = app(GetVipProductListAction::class)->execute();

        $this->assertSame([$included], $products->pluck('id')->all());
        $this->assertSame(1, $products->total());
    }

    public function test_page_is_public_and_its_slug_is_reserved_for_content(): void
    {
        $this->assertTrue(app(ReservedSlugService::class)->isReserved('vip-products'));
        $this->withoutMiddleware(ForceTrailingSlash::class);

        $product = $this->product('visible-vip-product', 'active', now());
        $this->saleItem($product, true);

        $this->get(front_route('vip.products.index'))
            ->assertOk()
            ->assertSee('محصولات دارای دسترسی VIP')
            ->assertSee('Product visible-vip-product')
            ->assertSee('دسترسی VIP');

        $this->get(front_route('vip.products.page', ['page' => 1]))
            ->assertRedirect(front_route('vip.products.index'));
    }

    private function product(string $slug, string $status, $publishedAt): int
    {
        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Test Store '.$slug,
            'slug' => 'test-store-'.$slug,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('products')->insertGetId([
            'store_id' => $storeId,
            'title' => 'Product '.$slug,
            'slug' => $slug,
            'type' => 'digital',
            'structure' => 'simple',
            'status' => $status,
            'published_at' => $publishedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function saleItem(
        int $productId,
        bool $vip,
        string $status = 'available',
        bool $hasFile = true,
        bool $launcherEnabled = false,
    ): void
    {
        $saleItemId = DB::table('product_sale_items')->insertGetId([
            'product_id' => $productId,
            'sku' => 'SKU-'.str()->random(8),
            'base_price' => 50_000,
            'status' => $status,
            'vip_access_enabled' => $vip,
            'launcher_enabled' => $launcherEnabled,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($hasFile) {
            DB::table('product_sale_item_files')->insert([
                'product_sale_item_id' => $saleItemId,
                'title' => 'VIP file',
                'url' => 'https://example.com/vip-file.zip',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
