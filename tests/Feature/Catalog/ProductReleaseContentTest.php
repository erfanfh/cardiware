<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Actions\SyncProductReleasesAction;
use App\Application\Catalog\Support\ProductEditLock;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductRelease;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProductReleaseContentTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        $store = Store::create(['title' => 'Store', 'slug' => 'release-store', 'status' => 'active']);

        return [
            'title' => 'Product', 'slug' => 'release-product', 'body' => '',
            'store_id' => $store->id, 'type' => 'digital', 'structure' => 'simple', 'status' => 'active',
        ];
    }

    public function test_migration_preserves_legacy_rows_and_converts_them_in_order_with_safe_html(): void
    {
        $migration = require database_path('migrations/2026_10_19_000001_add_changes_body_to_product_releases_table.php');
        $migration->down();
        $product = Product::withoutEvents(fn () => Product::create($this->payload() + ['published_at' => now()]));
        $release = $product->releases()->create(['title' => 'نسخه قدیمی']);
        $empty = $product->releases()->create(['title' => 'بدون تغییرات']);
        $release->releaseChanges()->createMany([
            ['title' => 'تغییر ویژه', 'sort_order' => 2, 'is_special' => true],
            ['title' => '<script>alert("x")</script> & متن', 'sort_order' => 1],
            ['title' => 'هم‌رتبه', 'sort_order' => 2],
        ]);
        $before = DB::table('product_release_changes')->orderBy('id')->get()->toJson();
        $product->delete(); // Include releases belonging to soft-deleted products.

        $migration->up();

        self::assertSame(
            '<ul><li>&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt; &amp; متن</li><li><strong>تغییر ویژه</strong></li><li>هم‌رتبه</li></ul>',
            $release->fresh()->changes_body
        );
        self::assertSame('', $empty->fresh()->changes_body);
        self::assertSame($before, DB::table('product_release_changes')->orderBy('id')->get()->toJson());
    }

    public function test_admin_saves_edits_and_clears_rich_content_without_recreating_legacy_rows(): void
    {
        Gate::before(fn () => true);
        $admin = User::factory()->createQuietly();
        $this->actingAs($admin);
        $payload = $this->payload();
        $body = '<h3>امکانات</h3><ul><li><strong>تغییر مهم</strong></li></ul><p><a href="https://example.test">جزئیات</a></p>';
        $payload['releases'] = [['title' => 'نسخه اول', 'changes_body' => $body]];

        $this->post(route('admin.products.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $product = Product::withoutGlobalScopes()->where('slug', $payload['slug'])->firstOrFail();
        $release = $product->releases()->firstOrFail();
        self::assertSame($body, $release->changes_body);
        $this->assertDatabaseCount('product_release_changes', 0);
        self::assertStringContainsString($body, view('front.catalog.products.partials.detail.releases', compact('product'))->render());

        $this->get(route('admin.products.edit', $product))->assertOk()
            ->assertSee('repeaterEditor()', false)->assertSee("field(i, 'changes_body')", false);
        $payload['edit_lock_token'] = app(ProductEditLock::class)->acquire($product, $admin);
        $payload['releases'][0]['id'] = $release->id;
        $payload['releases'][0]['changes_body'] = '<p>ویرایش جدید</p>';
        $this->put(route('admin.products.update', $product), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame('<p>ویرایش جدید</p>', $release->fresh()->changes_body);

        // Clearing must not revive archived legacy items through the fallback accessor.
        $release->releaseChanges()->create(['title' => 'آیتم قدیمی']);
        $payload['releases'][0]['changes_body'] = '';
        $this->put(route('admin.products.update', $product), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame('', $release->fresh()->changes_body);
        $this->assertDatabaseCount('product_release_changes', 1);

        $payload['releases'][0]['changes_body'] = ['invalid'];
        $this->putJson(route('admin.products.update', $product), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('releases.0.changes_body');
    }

    public function test_omitted_content_is_preserved_and_legacy_submissions_are_converted(): void
    {
        $product = Product::withoutEvents(fn () => Product::create($this->payload() + ['published_at' => now()]));
        $release = $product->releases()->create(['title' => 'نسخه', 'changes_body' => '<p>متن قبلی</p>']);
        $action = app(SyncProductReleasesAction::class);
        $action->execute($product, [['id' => $release->id, 'title' => 'عنوان جدید']]);
        self::assertSame('<p>متن قبلی</p>', $release->fresh()->changes_body);

        $action->execute($product, [['id' => $release->id, 'title' => 'نسخه', 'changes' => "عادی\n** ویژه"]]);
        self::assertSame('<ul><li>عادی</li><li><strong>ویژه</strong></li></ul>', $release->fresh()->changes_body);
    }

    public function test_cached_legacy_releases_render_as_lists_without_the_new_attribute(): void
    {
        $release = new ProductRelease(['title' => 'نسخه کش‌شده']);
        $release->setRelation('releaseChanges', collect([
            new \App\Domain\Catalog\Models\ProductReleaseChange(['title' => 'دوم', 'sort_order' => 2]),
            new \App\Domain\Catalog\Models\ProductReleaseChange(['title' => 'اول', 'sort_order' => 1, 'is_special' => true]),
        ]));
        self::assertSame('<ul><li><strong>اول</strong></li><li>دوم</li></ul>', $release->changes_body);
    }
}
