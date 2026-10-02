<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSellerAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_replace_and_remove_multiple_sellers(): void
    {
        $adminRole = Role::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.stores.store'), [
            'title' => 'فروشگاه تست',
            'slug' => 'seller-assignment-test',
            'status' => StoreStatusEnum::ACTIVE->value,
            'seller_user_ids' => [$first->id, $second->id],
        ])->assertRedirect();

        $store = Store::query()->where('title', 'فروشگاه تست')->firstOrFail();
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $store->sellers()->pluck('users.id')->all());

        $this->actingAs($admin)->put(route('admin.stores.update', $store), [
            'title' => $store->title,
            'slug' => $store->slug,
            'status' => StoreStatusEnum::ACTIVE->value,
            'seller_user_ids' => [$second->id, $third->id],
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing([$second->id, $third->id], $store->sellers()->pluck('users.id')->all());

        $this->actingAs($admin)->put(route('admin.stores.update', $store), [
            'title' => $store->title,
            'slug' => $store->slug,
            'status' => StoreStatusEnum::ACTIVE->value,
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing([$second->id, $third->id], $store->sellers()->pluck('users.id')->all());

        $this->actingAs($admin)->put(route('admin.stores.update', $store), [
            'title' => $store->title,
            'slug' => $store->slug,
            'status' => StoreStatusEnum::ACTIVE->value,
            'seller_user_ids_submitted' => '1',
        ])->assertRedirect();

        $this->assertCount(0, $store->sellers()->get());
    }

    public function test_invalid_or_duplicate_seller_ids_are_rejected(): void
    {
        $adminRole = Role::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $seller = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.stores.store'), [
            'title' => 'فروشگاه تست',
            'status' => StoreStatusEnum::ACTIVE->value,
            'seller_user_ids' => [$seller->id, $seller->id, 999999],
        ])->assertSessionHasErrors(['seller_user_ids.1', 'seller_user_ids.2']);

        $this->assertDatabaseCount('stores', 0);
    }

    public function test_store_form_displays_assigned_sellers_and_supports_search(): void
    {
        $adminRole = Role::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $seller = User::factory()->create(['display_name' => 'فروشنده نمونه']);
        $store = Store::query()->create([
            'title' => 'فروشگاه نمونه',
            'slug' => 'sample-store',
            'status' => StoreStatusEnum::ACTIVE,
        ]);
        $store->sellers()->attach($seller->id);

        $this->actingAs($admin)
            ->get(route('admin.stores.edit', $store))
            ->assertOk()
            ->assertSee('فروشنده نمونه')
            ->assertSee('seller_user_ids[]', false);

        $this->getJson(route('admin.stores.seller-search', ['q' => 'فروشنده نمونه']))
            ->assertOk()
            ->assertJsonPath('items.0.value', (string) $seller->id);
    }
}
