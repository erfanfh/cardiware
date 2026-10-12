<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Actions\GetProductDetailAction;
use App\Application\Organization\Actions\TeamMember\BulkDeleteTeamMembersAction;
use App\Application\Organization\Actions\TeamMember\DeleteTeamMemberAction;
use App\Application\Organization\Actions\TeamMember\UpdateTeamMemberAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Catalog\Models\Product;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Organization\Models\TeamMember;
use Illuminate\Cache\TaggedCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TeamMemberProductCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_member_refreshes_their_information_on_all_products(): void
    {
        $member = $this->createMember();
        $products = [$this->createProduct('first'), $this->createProduct('second')];

        foreach ($products as $product) {
            $this->attachAndCacheMember($product, $member);
        }

        Cache::tags([CacheTag::PRODUCT_LISTS])->put('unrelated-list', 'cached', 600);

        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Member',
            'job_title' => 'Developer',
            'bio' => 'Updated biography',
            'email' => 'updated@example.test',
            'mobile' => '09120000000',
            'website_url' => 'https://updated.example.test',
        ];

        app(UpdateTeamMemberAction::class)->execute($member, $data);

        foreach ($products as $product) {
            self::assertFalse($this->detailCache()->has($this->detailKey($product)));
            $detail = app(GetProductDetailAction::class)->execute($product->fresh());
            $updatedMember = $detail->teamMembers->sole()->teamMember;

            foreach ($data as $attribute => $value) {
                self::assertSame($value, $updatedMember->{$attribute});
            }

            self::assertTrue($product->updated_at->equalTo($detail->updated_at));
        }

        self::assertSame('cached', Cache::tags([CacheTag::PRODUCT_LISTS])->get('unrelated-list'));
    }

    public function test_deleting_a_member_removes_them_from_cached_product_details(): void
    {
        $member = $this->createMember();
        $remainingMember = $this->createMember();
        $product = $this->createProduct('single-delete');
        $product->teamMembers()->create(['team_member_id' => $remainingMember->id, 'sort_order' => 1]);
        $this->attachAndCacheMember($product, $member);

        app(DeleteTeamMemberAction::class)->execute($member);

        self::assertFalse($this->detailCache()->has($this->detailKey($product)));
        $detail = app(GetProductDetailAction::class)->execute($product->fresh());
        self::assertSame(
            [$remainingMember->id],
            $detail->teamMembers->pluck('teamMember')->filter()->pluck('id')->values()->all(),
        );
    }

    public function test_bulk_deleting_members_removes_them_from_cached_product_details(): void
    {
        $members = [$this->createMember(), $this->createMember()];
        $products = [$this->createProduct('bulk-first'), $this->createProduct('bulk-second')];

        foreach ($products as $index => $product) {
            $this->attachAndCacheMember($product, $members[$index]);
        }

        $count = app(BulkDeleteTeamMembersAction::class)->execute(array_map(fn ($member) => $member->id, $members));

        self::assertSame(2, $count);
        foreach ($products as $product) {
            self::assertFalse($this->detailCache()->has($this->detailKey($product)));
            $detail = app(GetProductDetailAction::class)->execute($product->fresh());
            self::assertTrue($detail->teamMembers->pluck('teamMember')->filter()->isEmpty());
        }
    }

    public function test_bulk_delete_without_matching_members_preserves_product_details(): void
    {
        $product = $this->createProduct('empty-delete');
        $this->attachAndCacheMember($product, $this->createMember());

        self::assertSame(0, app(BulkDeleteTeamMembersAction::class)->execute([]));
        self::assertTrue($this->detailCache()->has($this->detailKey($product)));
    }

    private function createMember(): TeamMember
    {
        return TeamMember::query()->create([
            'first_name' => 'Original',
            'last_name' => 'Member',
            'website_url' => 'https://original.example.test',
        ]);
    }

    private function createProduct(string $slug): Product
    {
        $store = Store::query()->create(['title' => 'Store', 'slug' => $slug, 'status' => 'active']);

        return Product::query()->create([
            'store_id' => $store->id,
            'title' => 'Product',
            'slug' => $slug,
            'type' => 'digital',
            'structure' => 'simple',
            'status' => 'active',
            'published_at' => now()->subMinute(),
        ]);
    }

    private function attachAndCacheMember(Product $product, TeamMember $member): void
    {
        $product->teamMembers()->create(['team_member_id' => $member->id]);
        $detail = app(GetProductDetailAction::class)->execute($product->fresh());

        self::assertTrue($this->detailCache()->has($this->detailKey($product)));
        self::assertSame($member->website_url, $detail->teamMembers->first()->teamMember->website_url);
    }

    private function detailCache(): TaggedCache
    {
        return Cache::tags([CacheTag::PRODUCT_DETAIL, CacheTag::PRODUCTS]);
    }

    private function detailKey(Product $product): string
    {
        return CacheKey::productDetail($product->id, $product->updated_at->timestamp);
    }
}
