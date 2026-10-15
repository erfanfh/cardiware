<?php

namespace Tests\Feature\Marketplace;

use App\Application\Catalog\Actions\GetProductDetailAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSalesVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_are_visible_by_default(): void
    {
        $store = Store::query()->create([
            'title' => 'Store', 'slug' => 'visible-store', 'status' => 'active',
        ])->fresh();

        self::assertFalse($store->hide_sales_count);
        $this->assertSalesVisibility($store, true);
    }

    public function test_admin_can_hide_and_show_sales_and_refresh_cached_product_details(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::factory()->create(['is_super_admin' => true])->id,
        ]);

        $this->actingAs($admin)->post(route('admin.stores.store'), [
            'title' => 'Store',
            'slug' => 'hidden-store',
            'status' => 'active',
            'hide_sales_count' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $store = Store::query()->where('slug', 'hidden-store')->firstOrFail();
        self::assertTrue($store->hide_sales_count);
        $this->assertSalesVisibility($store, false);

        $this->get(route('admin.stores.edit', $store))
            ->assertOk()->assertSee('عدم نمایش تعداد فروش')->assertSee('hide_sales_count');

        $product = Product::query()->create([
            'store_id' => $store->id,
            'title' => 'Product', 'slug' => 'visibility-product',
            'type' => 'digital', 'structure' => 'simple',
            'status' => 'active', 'published_at' => now()->subMinute(),
        ]);

        foreach ([false, true] as $hidden) {
            $detail = app(GetProductDetailAction::class)->execute($product->fresh());
            self::assertSame(!$hidden, $detail->store->hide_sales_count);

            $this->put(route('admin.stores.update', $store), [
                'title' => $store->title,
                'slug' => $store->slug,
                'status' => 'active',
                'hide_sales_count' => $hidden ? '1' : '0',
            ])->assertSessionHasNoErrors()->assertRedirect();

            $detail = app(GetProductDetailAction::class)->execute($product->fresh());
            self::assertSame($hidden, $detail->store->hide_sales_count);
            $this->assertSalesVisibility($store->fresh(), !$hidden);
        }
    }

    public function test_invalid_visibility_value_is_rejected(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::factory()->create(['is_super_admin' => true])->id,
        ]);

        $this->actingAs($admin)->post(route('admin.stores.store'), [
            'title' => 'Store', 'status' => 'active', 'hide_sales_count' => 'invalid',
        ])->assertSessionHasErrors('hide_sales_count');
    }

    private function assertSalesVisibility(Store $store, bool $visible): void
    {
        $product = (new Product())->setRelation('store', $store);
        $store->setRelation('picture', null);
        $store->setRelation('products', collect());
        $store->setAttribute('products_count', 7);

        $views = [
            $this->view('front.marketplace.stores.partials.detail.hero', [
                'store' => $store, 'soldQuantity' => 987654,
            ]),
            $this->view('front.catalog.products.partials.detail.sidebar.store', [
                'product' => $product, 'storeProducts' => 7, 'storeSoldQuantity' => 987654,
            ]),
        ];

        foreach ($views as $view) {
            $view->assertSee('تعداد محصول');
            if ($visible) {
                $view->assertSee('تعداد فروش');
            } else {
                $view->assertDontSee('تعداد فروش')
                    ->assertDontSee('987654')->assertDontSee('987,654');
            }
        }
    }
}
