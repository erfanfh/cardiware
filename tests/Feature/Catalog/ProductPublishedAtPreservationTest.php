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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductPublishedAtPreservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unrelated_edits_preserve_original_product_dates(): void
    {
        $store = Store::query()->create([
            'title' => 'Store',
            'slug' => 'date-test-store',
            'status' => StoreStatusEnum::ACTIVE,
        ]);
        $publishedAt = '2024-03-05 09:15:37';
        $createdAt = '2024-03-01 12:30:42';
        $product = Product::query()->create([
            'store_id' => $store->id,
            'title' => 'Original',
            'slug' => 'date-test-product',
            'type' => ProductTypeEnum::DIGITAL,
            'structure' => ProductStructureEnum::SIMPLE,
            'status' => ProductStatusEnum::ACTIVE,
            'published_at' => $publishedAt,
        ]);
        DB::table('products')->where('id', $product->id)->update(['created_at' => $createdAt]);
        $product->refresh();

        $role = Role::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($admin);

        $payload = [
            'title' => 'Edited',
            'body' => '',
            'slug' => $product->slug,
            'store_id' => $store->id,
            'type' => ProductTypeEnum::DIGITAL->value,
            'structure' => ProductStructureEnum::SIMPLE->value,
            'status' => ProductStatusEnum::ACTIVE->value,
        ];

        // Requests may omit the field, submit an empty date, or post the
        // unchanged minute-precision value rendered by the date picker.
        foreach ([
            $payload,
            [...$payload, 'published_at' => ''],
            [...$payload, 'published_at' => jalali_date($product->published_at)],
        ] as $requestPayload) {
            $this->put(route('admin.products.update', $product), $requestPayload)
                ->assertRedirect();

            $product->refresh();
            $this->assertSame($publishedAt, $product->published_at->format('Y-m-d H:i:s'));
            $this->assertSame($createdAt, $product->created_at->format('Y-m-d H:i:s'));
        }

        $changedDate = '2024-03-06 09:15:00';
        $this->put(route('admin.products.update', $product), [
            ...$payload,
            'published_at' => jalali_date($changedDate),
        ])->assertRedirect();

        $this->assertSame($changedDate, $product->fresh()->published_at->format('Y-m-d H:i:s'));
    }
}
