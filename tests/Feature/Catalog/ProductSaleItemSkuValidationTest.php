<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Support\ProductEditLock;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProductSaleItemSkuValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);
        $this->actingAs(User::factory()->createQuietly());
        Store::create(['title' => 'Store', 'slug' => 'sku-store', 'status' => 'active']);
    }

    public function test_create_rejects_an_existing_active_sku_before_saving(): void
    {
        $this->createProduct('original', [['sku' => 'TAKEN']]);

        $this->postJson(route('admin.products.store'), $this->payload('new', [['sku' => ' TAKEN ']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sale_items.0.sku');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_sale_items', 1);
    }

    public function test_update_rejects_another_items_sku_and_preserves_the_existing_data(): void
    {
        $product = $this->createProduct('original', [['sku' => 'ORIGINAL']]);
        $this->createProduct('other', [['sku' => 'TAKEN']]);
        $item = $product->saleItems()->firstOrFail();

        $this->putJson(route('admin.products.update', $product), $this->updatePayload($product, [
            ['id' => $item->id, 'sku' => 'TAKEN'],
        ]))->assertUnprocessable()->assertJsonValidationErrors('sale_items.0.sku');

        self::assertSame('ORIGINAL', $item->fresh()->sku);
    }

    public function test_update_allows_keeping_the_items_own_sku(): void
    {
        $product = $this->createProduct('original', [['sku' => 'ORIGINAL']]);
        $item = $product->saleItems()->firstOrFail();

        $this->put(route('admin.products.update', $product), $this->updatePayload($product, [
            ['id' => $item->id, 'sku' => 'ORIGINAL', 'base_price' => 12345],
        ]))->assertSessionHasNoErrors()->assertRedirect();

        self::assertSame(12345, $item->fresh()->base_price);
        $this->assertDatabaseCount('product_sale_items', 1);
    }

    public function test_create_and_update_reject_duplicate_skus_within_the_payload(): void
    {
        $items = [['sku' => 'DUPLICATE'], ['sku' => ' DUPLICATE ']];
        $this->postJson(route('admin.products.store'), $this->payload('new', $items))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sale_items.0.sku', 'sale_items.1.sku']);
        $this->assertDatabaseCount('products', 0);

        $product = $this->createProduct('original', [['sku' => 'ORIGINAL']]);
        $this->putJson(route('admin.products.update', $product), $this->updatePayload($product, $items))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sale_items.0.sku', 'sale_items.1.sku']);
        self::assertSame('ORIGINAL', $product->saleItems()->firstOrFail()->sku);
    }

    public function test_duplicate_skus_are_case_insensitive_within_a_payload(): void
    {
        $this->postJson(route('admin.products.store'), $this->payload('new', [
            ['sku' => 'Duplicate'], ['sku' => 'duplicate'],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['sale_items.0.sku', 'sale_items.1.sku']);
    }

    public function test_multiple_items_can_have_an_empty_sku(): void
    {
        $product = $this->createProduct('original', [
            ['sku' => '', 'title' => 'First'], ['sku' => null, 'title' => 'Second'],
        ]);

        self::assertSame(2, $product->saleItems()->whereNull('sku')->count());
    }

    public function test_deleted_skus_can_be_reused_on_create_and_update_without_changing_history(): void
    {
        $original = $this->createProduct('original', [['sku' => 'REUSABLE']]);
        $deletedItem = $original->saleItems()->firstOrFail();
        $deletedItem->delete();

        $replacement = $this->createProduct('replacement', [['sku' => 'REUSABLE']]);
        $replacementItem = $replacement->saleItems()->firstOrFail();
        $replacementItem->delete();

        $target = $this->createProduct('target', [['sku' => 'TARGET']]);
        $targetItem = $target->saleItems()->firstOrFail();
        $this->put(route('admin.products.update', $target), $this->updatePayload($target, [
            ['id' => $targetItem->id, 'sku' => 'REUSABLE'],
        ]))->assertSessionHasNoErrors()->assertRedirect();

        self::assertSame('REUSABLE', $targetItem->fresh()->sku);
        self::assertSame('REUSABLE', $deletedItem->fresh()->sku);
        self::assertTrue($deletedItem->fresh()->trashed());
        self::assertSame(2, ProductSaleItem::onlyTrashed()->where('sku', 'REUSABLE')->count());
    }

    public function test_another_products_item_id_cannot_bypass_sku_validation(): void
    {
        $other = $this->createProduct('other', [['sku' => 'TAKEN']]);
        $item = $other->saleItems()->firstOrFail();
        $product = $this->createProduct('target', [['sku' => 'TARGET']]);

        $this->putJson(route('admin.products.update', $product), $this->updatePayload($product, [
            ['id' => $item->id, 'sku' => 'TAKEN'],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['sale_items.0.id', 'sale_items.0.sku']);
    }

    public function test_database_still_prevents_duplicate_active_skus(): void
    {
        $product = $this->createProduct('original', [['sku' => 'TAKEN']]);

        $this->expectException(UniqueConstraintViolationException::class);
        $product->saleItems()->create(['sku' => 'TAKEN', 'status' => 'available']);
    }

    public function test_migration_preserves_existing_rows_and_allows_reusing_deleted_skus(): void
    {
        $product = $this->createProduct('original', [['sku' => 'ORIGINAL'], ['sku' => 'DELETED']]);
        $deletedItem = $product->saleItems()->where('sku', 'DELETED')->firstOrFail();
        $deletedItem->delete();
        $migration = require database_path('migrations/2026_11_13_000002_allow_reusing_soft_deleted_sale_item_skus.php');

        $migration->down();
        $migration->up();

        self::assertSame('ORIGINAL', $product->saleItems()->firstOrFail()->sku);
        self::assertTrue($deletedItem->fresh()->trashed());
        $replacement = $product->saleItems()->create(['sku' => 'DELETED', 'status' => 'available']);
        self::assertNotSame($deletedItem->id, $replacement->id);
        $this->assertDatabaseCount('product_sale_items', 3);
    }

    private function createProduct(string $slug, array $items): Product
    {
        $this->post(route('admin.products.store'), $this->payload($slug, $items))
            ->assertSessionHasNoErrors()->assertRedirect();

        return Product::withoutGlobalScopes()->where('slug', $slug)->firstOrFail();
    }

    private function updatePayload(Product $product, array $items): array
    {
        return $this->payload($product->slug, $items) + [
            'edit_lock_token' => app(ProductEditLock::class)->acquire($product, auth()->user()),
        ];
    }

    private function payload(string $slug, array $items): array
    {
        return [
            'title' => 'SKU product',
            'slug' => $slug,
            'body' => 'Product description',
            'store_id' => Store::query()->firstOrFail()->id,
            'type' => 'digital',
            'structure' => 'variable',
            'status' => 'active',
            'sale_items' => $items,
        ];
    }
}
