<?php

namespace Tests\Feature\Marketplace;

use App\Application\Marketplace\Actions\DeleteStoreAction;
use App\Application\Marketplace\Actions\HandleStoreBulkAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StoreSlugCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_warm_slug_cache_skips_the_registry_query_but_loads_a_fresh_store(): void
    {
        $store = $this->createStore();
        self::assertSame($store->id, $this->resolve('first-store')->model->id);

        // Bypass model events to prove the resolver does not cache the store itself.
        DB::table('stores')->where('id', $store->id)->update([
            'title' => 'Updated title',
            'status' => 'inactive',
        ]);
        DB::enableQueryLog();
        DB::flushQueryLog();

        $resolved = $this->resolve('first-store');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        self::assertTrue($resolved->isCurrent);
        self::assertSame('Updated title', $resolved->model->title);
        self::assertSame(StoreStatusEnum::INACTIVE, $resolved->model->status);
        self::assertCount(1, $queries);
        self::assertStringContainsString('"stores"', $queries[0]['query']);
    }

    public function test_valid_slug_mapping_has_no_time_expiry(): void
    {
        $this->createStore();
        $this->resolve('first-store');
        $this->travel(30)->days();

        self::assertTrue(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertTrue($this->resolve('first-store')->found);
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
        self::assertTrue(Cache::has(CacheKey::storeSlug('missing')));
        $this->travel(2)->minutes();
        self::assertFalse(Cache::has(CacheKey::storeSlug('missing')));
    }

    public function test_assigning_a_new_slug_clears_a_cached_miss_immediately(): void
    {
        self::assertFalse($this->resolve('first-store')->found);
        $store = $this->createStore();

        self::assertFalse(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertSame($store->id, $this->resolve('first-store')->model->id);
    }

    public function test_renaming_refreshes_all_historical_redirects_and_a_cached_miss(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        $store = $this->createStore();
        $this->resolve('first-store');
        $this->assign($store, 'second-store');
        self::assertSame(route('stores.detail', 'second-store'), $this->resolve('first-store')->redirectTo);
        $this->resolve('second-store');
        self::assertFalse($this->resolve('third-store')->found);

        $this->assign($store, 'third-store');

        foreach (['first-store', 'second-store', 'third-store'] as $slug) {
            self::assertFalse(Cache::has(CacheKey::storeSlug($slug)));
        }

        foreach (['first-store', 'second-store'] as $slug) {
            $resolved = $this->resolve($slug);
            self::assertTrue($resolved->shouldRedirect);
            self::assertSame(route('stores.detail', 'third-store'), $resolved->redirectTo);
            $this->get(route('stores.detail', $slug))
                ->assertStatus(301)->assertRedirect(route('stores.detail', 'third-store'));
        }

        self::assertTrue($this->resolve('third-store')->isCurrent);

        $this->assign($store, 'first-store');
        self::assertTrue($this->resolve('first-store')->isCurrent);
        self::assertSame(route('stores.detail', 'first-store'), $this->resolve('third-store')->redirectTo);
    }

    public function test_a_cached_mapping_does_not_expose_a_deleted_store_or_redirect_to_it(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        $store = $this->createStore();
        $this->assign($store, 'second-store');
        $this->resolve('first-store');
        $this->resolve('second-store');

        // Keep the mappings warm to verify the check independently of invalidation.
        DB::table('stores')->where('id', $store->id)->update(['deleted_at' => now()]);

        foreach (['first-store', 'second-store'] as $slug) {
            self::assertTrue(Cache::has(CacheKey::storeSlug($slug)));
            self::assertFalse($this->resolve($slug)->found);
            $this->get(route('stores.detail', $slug))->assertNotFound();
        }
    }

    public function test_updates_deletion_and_restoration_clear_current_and_historical_keys(): void
    {
        $store = $this->createStore();
        $this->assign($store, 'second-store');
        $this->resolve('first-store');
        $this->resolve('second-store');

        $store->update(['status' => 'inactive']);
        self::assertFalse(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertFalse(Cache::has(CacheKey::storeSlug('second-store')));
        self::assertSame(StoreStatusEnum::INACTIVE, $this->resolve('second-store')->model->status);

        $store->update(['status' => 'active']);
        self::assertTrue($this->resolve('second-store')->found);
        $this->resolve('first-store');
        self::assertTrue(app(DeleteStoreAction::class)->execute($store)->status);

        self::assertFalse(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertFalse(Cache::has(CacheKey::storeSlug('second-store')));
        self::assertFalse($this->resolve('first-store')->found);
        self::assertFalse($this->resolve('second-store')->found);

        $store->restore();
        self::assertFalse(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertFalse(Cache::has(CacheKey::storeSlug('second-store')));
        self::assertTrue($this->resolve('second-store')->found);
    }

    public function test_bulk_deletion_clears_slug_cache_without_flushing_other_stores(): void
    {
        $store = $this->createStore();
        $this->createStore('other-store');
        $this->resolve('first-store');
        $this->resolve('other-store');

        app(HandleStoreBulkAction::class)->execute('delete', [$store->id]);

        self::assertFalse(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertFalse($this->resolve('first-store')->found);
        self::assertTrue(Cache::has(CacheKey::storeSlug('other-store')));
        self::assertTrue($this->resolve('other-store')->found);
    }

    public function test_registry_key_changes_and_deletion_invalidate_cached_entries(): void
    {
        $this->createStore();
        $this->resolve('first-store');
        $this->resolve('replacement');
        $record = StoreSlug::query()->where('route_key', 'first-store')->firstOrFail();

        DB::transaction(fn () => $record->update(['route_key' => 'replacement']));

        self::assertFalse(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertFalse(Cache::has(CacheKey::storeSlug('replacement')));
        self::assertFalse($this->resolve('first-store')->found);
        self::assertTrue($this->resolve('replacement')->shouldRedirect);

        $record->delete();
        self::assertFalse(Cache::has(CacheKey::storeSlug('replacement')));
        self::assertFalse($this->resolve('replacement')->found);
    }

    public function test_invalidation_waits_for_commit_and_is_discarded_on_rollback(): void
    {
        $store = $this->createStore();
        $this->resolve('first-store');

        DB::beginTransaction();
        $this->assign($store, 'second-store');
        self::assertTrue(Cache::has(CacheKey::storeSlug('first-store')));
        DB::rollBack();

        self::assertTrue(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertTrue($this->resolve('first-store')->isCurrent);
        self::assertFalse($this->resolve('second-store')->found);

        DB::transaction(fn () => $this->assign($store->fresh(), 'second-store'));

        self::assertFalse(Cache::has(CacheKey::storeSlug('first-store')));
        self::assertFalse(Cache::has(CacheKey::storeSlug('second-store')));
        self::assertTrue($this->resolve('first-store')->shouldRedirect);
        self::assertTrue($this->resolve('second-store')->isCurrent);
    }

    private function createStore(string $slug = 'first-store'): Store
    {
        $store = Store::query()->create(['title' => 'Store', 'status' => 'active']);
        $this->assign($store, $slug);

        return $store;
    }

    private function assign(Store $store, string $slug): void
    {
        app(AssignSlugAction::class)->execute(new AssignSlugDTO(
            scope: SlugScopeEnum::STORE,
            model: $store,
            desiredSlug: $slug,
        ));
    }

    private function resolve(string $slug): ResolvedSlugDTO
    {
        return app(ResolveSlugAction::class)->execute(new ResolveSlugDTO(SlugScopeEnum::STORE, $slug));
    }
}
