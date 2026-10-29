<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Actions\GetProductListPageDataAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductCategoryListDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('taxonomy_terms', function (Blueprint $table): void {
            $table->id();
            $table->string('taxonomy');
            $table->string('title');
            $table->string('slug');
            $table->string('path');
            $table->foreignId('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('type')->default('digital');
            $table->string('structure')->default('simple');
            $table->string('status');
            $table->unsignedBigInteger('picture_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id');
            $table->unsignedBigInteger('base_price');
            $table->string('status')->default('available');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_sale_item_discounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_sale_item_id');
            $table->unsignedBigInteger('discounted_price');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('taxonomables', function (Blueprint $table): void {
            $table->foreignId('taxonomy_term_id');
            $table->string('taxonomable_type');
            $table->unsignedBigInteger('taxonomable_id');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropAllTables();

        parent::tearDown();
    }

    public function test_parent_category_lists_descendant_products_and_excludes_other_branches(): void
    {
        $parentId = $this->insertCategory('بازی‌ها', 'games');
        $childId = $this->insertCategory('ورزشی', 'games/sports', $parentId);
        $otherId = $this->insertCategory('نرم‌افزار', 'software');
        $includedProductId = $this->insertProduct('محصول ورزشی', 120_000, $childId);
        $this->insertProduct('محصول نامرتبط', 900_000, $otherId);

        $parent = TaxonomyTerm::query()->findOrFail($parentId);
        $data = app(GetProductListPageDataAction::class)->execute(
            request: Request::create('/product-category/games', 'GET'),
            fixedCategory: $parent,
        );

        self::assertSame([$includedProductId], $data['products']->pluck('id')->all());
        self::assertSame(120_000, $data['availablePriceMin']);
        self::assertSame(120_000, $data['availablePriceMax']);
        self::assertSame([$childId], $data['categories']->pluck('id')->all());
        self::assertTrue($data['featuredProducts']->contains('id', $includedProductId));
    }

    public function test_product_tag_lists_only_products_assigned_to_that_tag(): void
    {
        $tagId = $this->insertTag('گرافیک', 'graphics');
        $otherTagId = $this->insertTag('گیم‌پلی', 'gameplay');
        $includedProductId = $this->insertProduct('محصول گرافیکی', 180_000, $tagId);
        $this->insertProduct('محصول نامرتبط', 760_000, $otherTagId);

        $tag = TaxonomyTerm::query()->findOrFail($tagId);
        $data = app(GetProductListPageDataAction::class)->execute(
            request: Request::create('/product-tag/graphics', 'GET'),
            fixedTag: $tag,
        );

        self::assertSame([$includedProductId], $data['products']->pluck('id')->all());
        self::assertSame(180_000, $data['availablePriceMin']);
        self::assertSame(180_000, $data['availablePriceMax']);
        self::assertTrue($data['featuredProducts']->contains('id', $includedProductId));
    }

    public function test_discounts_are_loaded_with_the_list_and_price_edits_invalidate_cached_cards(): void
    {
        $categoryId = $this->insertCategory('Games', 'games');
        $productId = $this->insertProduct('Game', 180_000, $categoryId);
        $saleItemId = DB::table('product_sale_items')->where('product_id', $productId)->value('id');
        $discount = ProductSaleItemDiscount::query()->create([
            'product_sale_item_id' => $saleItemId, 'discounted_price' => 150_000,
        ]);
        $action = app(GetProductListPageDataAction::class);
        $read = fn () => $action->execute(Request::create('/products'))['products']->first()->saleItems->sole();
        $saleItem = $read();
        self::assertTrue($saleItem->relationLoaded('discount'));

        DB::enableQueryLog();
        DB::flushQueryLog();
        self::assertSame(150_000, $saleItem->discount->discounted_price);
        self::assertSame([], DB::getQueryLog());
        DB::disableQueryLog();

        $discount->update(['discounted_price' => 120_000]);
        self::assertSame(120_000, $read()->discount->discounted_price);
        $discount->delete();
        self::assertNull($read()->discount);
    }

    private function insertCategory(string $title, string $path, ?int $parentId = null): int
    {
        return DB::table('taxonomy_terms')->insertGetId([
            'taxonomy' => TaxonomyKeyEnum::PRODUCT_CATEGORY->value,
            'title' => $title,
            'slug' => basename(str_replace('\\', '/', $path)),
            'path' => $path,
            'parent_id' => $parentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertTag(string $title, string $path): int
    {
        return DB::table('taxonomy_terms')->insertGetId([
            'taxonomy' => TaxonomyKeyEnum::PRODUCT_TAG->value,
            'title' => $title,
            'slug' => $path,
            'path' => $path,
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertProduct(string $title, int $price, int $categoryId): int
    {
        $productId = DB::table('products')->insertGetId([
            'title' => $title,
            'slug' => str()->slug($title).'-'.str()->random(5),
            'status' => 'active',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_sale_items')->insert([
            'product_id' => $productId,
            'base_price' => $price,
            'status' => 'available',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('taxonomables')->insert([
            'taxonomy_term_id' => $categoryId,
            'taxonomable_type' => (new Product)->getMorphClass(),
            'taxonomable_id' => $productId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $productId;
    }
}
