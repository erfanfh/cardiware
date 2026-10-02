<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_unassigned_user_cannot_view_seller_dashboard(): void
    {
        $this->get(route('seller.dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('seller.dashboard'))
            ->assertForbidden();
    }

    public function test_assigned_user_only_sees_and_opens_their_own_stores(): void
    {
        $seller = User::factory()->create();
        $ownStore = $this->createStore('own-store', 'فروشگاه من');
        $otherStore = $this->createStore('other-store', 'فروشگاه دیگر');
        $ownStore->sellers()->attach($seller->id);

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('فروشگاه من')
            ->assertDontSee('فروشگاه دیگر');

        $this->get(route('seller.stores.dashboard', $ownStore))
            ->assertOk()
            ->assertSee('پنل فروشگاه');

        $this->get(route('seller.stores.dashboard', $otherStore))
            ->assertForbidden();

        $ownStore->sellers()->detach($seller->id);

        $this->get(route('seller.stores.dashboard', $ownStore))
            ->assertForbidden();
    }

    public function test_multiple_users_can_access_the_same_store(): void
    {
        $store = $this->createStore('shared-store', 'فروشگاه مشترک');
        $firstSeller = User::factory()->create();
        $secondSeller = User::factory()->create();
        $store->sellers()->attach([$firstSeller->id, $secondSeller->id]);

        $this->actingAs($firstSeller)
            ->get(route('seller.stores.dashboard', $store))
            ->assertOk();

        $this->actingAs($secondSeller)
            ->get(route('seller.stores.dashboard', $store))
            ->assertOk();
    }

    private function createStore(string $slug, string $title): Store
    {
        return Store::query()->create([
            'slug' => $slug,
            'title' => $title,
            'status' => StoreStatusEnum::ACTIVE,
        ]);
    }
}
