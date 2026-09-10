<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Actions\GetProductListPageDataAction;
use App\Domain\Catalog\Models\Product;
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
