<?php

namespace Tests\Feature\Vip;

use App\Application\Commerce\Actions\Checkout\CompleteOrderAfterSuccessfulPaymentAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VipUserListingTest extends TestCase
{
    use RefreshDatabase;

    private function buy(User $user, VipPlan $plan): VipSubscription
    {
        $this->actingAs($user)->post(route('vip.cart.add', $plan));

        $cart = Cart::query()->where('user_id', $user->id)->active()->firstOrFail();
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($order);

        return VipSubscription::query()->where('order_id', $order->id)->firstOrFail();
    }

    public function test_admin_user_list_shows_active_vip_and_remaining_days_including_renewals(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 12:00:00'));

        $plan = VipPlan::query()->create([
            'code' => 'vip-month',
            'title' => 'پلن ماهانه',
            'duration_days' => 30,
            'price_toman' => 100000,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $renewed = User::factory()->create();
        $expiring = User::factory()->create();
        $expired = User::factory()->create();
        $revoked = User::factory()->create();
        $scheduled = User::factory()->create();

        $this->buy($renewed, $plan);
        $this->buy($renewed, $plan);
        $this->buy($expiring, $plan)->update(['expires_at' => now()->addHours(5)]);
        $this->buy($expired, $plan)->update(['expires_at' => now()->subMinute()]);
        $this->buy($revoked, $plan)->update(['revoked_at' => now()]);
        $this->buy($scheduled, $plan)->update([
            'starts_at' => now()->addDay(),
            'expires_at' => now()->addDays(31),
        ]);

        $role = Role::factory()->create(['is_super_admin' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('اشتراک VIP')
            ->assertSee('60 روز باقی‌مانده')
            ->assertSee('1 روز باقی‌مانده')
            ->assertViewHas('users', function ($users) use ($renewed, $expiring, $expired, $revoked, $scheduled) {
                $rows = $users->getCollection()->keyBy('id');

                return (bool) $rows[$renewed->id]->has_active_vip
                    && Carbon::parse($rows[$renewed->id]->vip_expires_at)->equalTo(now()->addDays(60))
                    && (bool) $rows[$expiring->id]->has_active_vip
                    && ! $rows[$expired->id]->has_active_vip
                    && ! $rows[$revoked->id]->has_active_vip
                    && ! $rows[$scheduled->id]->has_active_vip;
            });
    }
}
