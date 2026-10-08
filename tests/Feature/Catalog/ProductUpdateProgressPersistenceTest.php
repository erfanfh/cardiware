<?php

namespace Tests\Feature\Catalog;

use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUpdateProgressPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_box_title_and_description_are_saved_when_creating_and_editing_a_product(): void
    {
        $store = Store::query()->create([
            'title' => 'Store',
            'slug' => 'progress-test-store',
            'status' => StoreStatusEnum::ACTIVE,
        ]);

        $role = Role::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($admin);

        $productData = [
            'title' => 'Product',
            'body' => '',
            'slug' => 'progress-test-product',
            'store_id' => $store->id,
            'type' => ProductTypeEnum::DIGITAL->value,
            'structure' => ProductStructureEnum::SIMPLE->value,
            'status' => ProductStatusEnum::ACTIVE->value,
        ];

        $this->post(route('admin.products.store'), [
            ...$productData,
            'update_progress' => [
                'title' => 'عنوان اولیه',
                'description' => 'توضیحات اولیه',
            ],
        ])->assertRedirect();

        $product = Product::query()->where('slug', 'progress-test-product')->firstOrFail();
        $this->assertSame('عنوان اولیه', $product->updateProgress->title);
        $this->assertSame('توضیحات اولیه', $product->updateProgress->description);

        $this->put(route('admin.products.update', $product), [
            ...$productData,
            'update_progress' => [
                'title' => 'عنوان ویرایش‌شده',
                'description' => 'توضیحات ویرایش‌شده',
            ],
        ])->assertRedirect();

        $product->refresh();
        $this->assertSame('عنوان ویرایش‌شده', $product->updateProgress->title);
        $this->assertSame('توضیحات ویرایش‌شده', $product->updateProgress->description);
    }
}
