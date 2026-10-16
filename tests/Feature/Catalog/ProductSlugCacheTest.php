<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Actions\DeleteProductAction;
use App\Application\Catalog\Actions\HandleProductBulkAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSlug;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductSlugCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_warm_slug_cache_skips_the_registry_query_but_loads_a_fresh_product(): void
    {
        $product = $this->createProduct();
        self::assertSame($product->id, $this->resolve('first-product')->model->id);

        // Bypass model events to prove the resolver does not cache the product itself.
        DB::table('products')->where('id', $product->id)->update(['title' => 'Updated title']);
        DB::enableQueryLog();
        DB::flushQueryLog();

        $resolved = $this->resolve('first-product');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        self::assertTrue($resolved->isCurrent);
        self::assertSame('Updated title', $resolved->model->title);
        self::assertCount(1, $queries);
        self::assertStringContainsString('"products"', $queries[0]['query']);
    }

    public function test_valid_slug_mapping_has_no_time_expiry(): void
    {
        $this->createProduct();
        $this->resolve('first-product');
        $this->travel(30)->days();

        self::assertTrue(Cache::has(CacheKey::productSlug('first-product')));
        self::assertTrue($this->resolve('first-product')->found);
    }

    public function test_missing_slug_is_cached_for_ten_minutes(): void
    {
        self::assertFalse($this->resolve('missing')->found);

        DB::enableQueryLog();
        DB::flushQueryLog();
        self::assertFalse($this->resolve('missing')->found);
        self::assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();

        $this->travel(9)->minutes();
        self::assertTrue(Cache::has(CacheKey::productSlug('missing')));
        $this->travel(2)->minutes();
        self::assertFalse(Cache::has(CacheKey::productSlug('missing')));
    }

    public function test_assigning_a_new_slug_clears_a_cached_miss_immediately(): void
    {
        self::assertFalse($this->resolve('first-product')->found);
        $product = $this->createProduct();

        self::assertFalse(Cache::has(CacheKey::productSlug('first-product')));
        self::assertSame($product->id, $this->resolve('first-product')->model->id);
    }

    public function test_renaming_refreshes_all_historical_redirects_and_a_cached_miss(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        $product = $this->createProduct();
        $this->resolve('first-product');
        $this->assign($product, 'second-product');
        self::assertSame(route('products.detail', 'second-product'), $this->resolve('first-product')->redirectTo);
        $this->resolve('second-product');
        self::assertFalse($this->resolve('third-product')->found);

        $this->assign($product, 'third-product');

        foreach (['first-product', 'second-product', 'third-product'] as $slug) {
            self::assertFalse(Cache::has(CacheKey::productSlug($slug)));
        }

        foreach (['first-product', 'second-product'] as $slug) {
            $resolved = $this->resolve($slug);
            self::assertTrue($resolved->shouldRedirect);
            self::assertSame(route('products.detail', 'third-product'), $resolved->redirectTo);
            // Exercise the cached redirect through the public controller too.
            $this->get(route('products.detail', $slug))
                ->assertStatus(301)->assertRedirect(route('products.detail', 'third-product'));
        }

        self::assertTrue($this->resolve('third-product')->isCurrent);

        $this->assign($product, 'first-product');
        self::assertTrue($this->resolve('first-product')->isCurrent);
        self::assertSame(route('products.detail', 'first-product'), $this->resolve('third-product')->redirectTo);
    }

    public function test_cached_mapping_rechecks_product_visibility_on_every_request(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        $product = $this->createProduct();
        $this->resolve('first-product');

        // Keep the mapping warm to verify the check independently of invalidation.
        DB::table('products')->where('id', $product->id)->update(['status' => 'inactive']);
        self::assertFalse($this->resolve('first-product')->found);
        $this->get(route('products.detail', 'first-product'))->assertNotFound();

        DB::table('products')->where('id', $product->id)->update([
            'status' => 'active',
            'published_at' => now()->addHour(),
        ]);
        self::assertFalse($this->resolve('first-product')->found);
        $this->travel(61)->minutes();
        self::assertTrue($this->resolve('first-product')->found);

        DB::table('products')->where('id', $product->id)->update(['deleted_at' => now()]);
        self::assertFalse($this->resolve('first-product')->found);
    }

    public function test_updates_deletion_and_restoration_clear_current_and_historical_keys(): void
    {
        $product = $this->createProduct();
        $this->assign($product, 'second-product');
        $this->resolve('first-product');
        $this->resolve('second-product');

        $product->update(['status' => 'inactive']);
        self::assertFalse(Cache::has(CacheKey::productSlug('first-product')));
        self::assertFalse(Cache::has(CacheKey::productSlug('second-product')));
        self::assertFalse($this->resolve('second-product')->found);

        $product->update(['status' => 'active']);
        self::assertTrue($this->resolve('second-product')->found);
        $this->resolve('first-product');
        app(DeleteProductAction::class)->execute($product);

        self::assertFalse(Cache::has(CacheKey::productSlug('first-product')));
        self::assertFalse(Cache::has(CacheKey::productSlug('second-product')));
        self::assertFalse($this->resolve('first-product')->found);
        self::assertFalse($this->resolve('second-product')->found);

        $product->restore();
        self::assertFalse(Cache::has(CacheKey::productSlug('first-product')));
        self::assertFalse(Cache::has(CacheKey::productSlug('second-product')));
        self::assertTrue($this->resolve('second-product')->found);
    }

    public function test_bulk_deletion_clears_slug_cache_without_flushing_other_products(): void
    {
        $product = $this->createProduct();
        $this->createProduct('other-product');
        $this->resolve('first-product');
        $this->resolve('other-product');

        app(HandleProductBulkAction::class)->execute('delete', [$product->id]);

        self::assertFalse(Cache::has(CacheKey::productSlug('first-product')));
        self::assertFalse($this->resolve('first-product')->found);
        self::assertTrue(Cache::has(CacheKey::productSlug('other-product')));
        self::assertTrue($this->resolve('other-product')->found);
    }

    public function test_registry_key_changes_and_deletion_invalidate_cached_entries(): void
    {
        $this->createProduct();
        $this->resolve('first-product');
        $this->resolve('replacement');
        $record = ProductSlug::query()->where('route_key', 'first-product')->firstOrFail();

        DB::transaction(fn () => $record->update(['route_key' => 'replacement']));

        self::assertFalse(Cache::has(CacheKey::productSlug('first-product')));
        self::assertFalse(Cache::has(CacheKey::productSlug('replacement')));
        self::assertFalse($this->resolve('first-product')->found);
        self::assertTrue($this->resolve('replacement')->shouldRedirect);

        $record->delete();
        self::assertFalse(Cache::has(CacheKey::productSlug('replacement')));
        self::assertFalse($this->resolve('replacement')->found);
    }

    public function test_invalidation_waits_for_commit_and_is_discarded_on_rollback(): void
    {
        $product = $this->createProduct();
        $this->resolve('first-product');

        DB::beginTransaction();
        $this->assign($product, 'second-product');
        self::assertTrue(Cache::has(CacheKey::productSlug('first-product')));
        DB::rollBack();

        self::assertTrue(Cache::has(CacheKey::productSlug('first-product')));
        self::assertTrue($this->resolve('first-product')->isCurrent);
        self::assertFalse($this->resolve('second-product')->found);

        DB::transaction(fn () => $this->assign($product->fresh(), 'second-product'));

        self::assertFalse(Cache::has(CacheKey::productSlug('first-product')));
        self::assertFalse(Cache::has(CacheKey::productSlug('second-product')));
        self::assertTrue($this->resolve('first-product')->shouldRedirect);
        self::assertTrue($this->resolve('second-product')->isCurrent);
    }

    private function createProduct(string $slug = 'first-product'): Product
    {
        $store = Store::query()->create(['title' => 'Store', 'slug' => $slug, 'status' => 'active']);
        $product = Product::query()->create([
            'store_id' => $store->id,
            'title' => 'Product',
            'type' => 'digital',
            'structure' => 'simple',
            'status' => 'active',
            'published_at' => now()->subMinute(),
        ]);
        $this->assign($product, $slug);

        return $product;
    }

    private function assign(Product $product, string $slug): void
    {
        app(AssignSlugAction::class)->execute(new AssignSlugDTO(
            scope: SlugScopeEnum::PRODUCT,
            model: $product,
            desiredSlug: $slug,
        ));
    }

    private function resolve(string $slug): ResolvedSlugDTO
    {
        return app(ResolveSlugAction::class)->execute(new ResolveSlugDTO(SlugScopeEnum::PRODUCT, $slug));
    }
}
