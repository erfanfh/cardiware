<?php

namespace Tests\Feature\Vip;

use App\Application\Commerce\Actions\Checkout\CompleteOrderAfterSuccessfulPaymentAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Application\Vip\Actions\RevokeVipSubscriptionsForOrderAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VipPlanPurchaseListingTest extends TestCase
{
    use RefreshDatabase;

    private function plan(string $code): VipPlan
    {
        return VipPlan::query()->create([
            'code' => $code,
            'title' => 'پلن '.$code,
            'duration_days' => 30,
            'price_toman' => 100000,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function buy(User $user, VipPlan $plan): VipSubscription
    {
        $this->actingAs($user)
            ->post(route('vip.cart.add', $plan))
            ->assertRedirect(route('cart.index'));

        $cart = Cart::query()->where('user_id', $user->id)->active()->firstOrFail();
        $order = app(CreateOrderFromCartAction::class)->execute($cart);
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($order);

        return VipSubscription::query()->where('order_id', $order->id)->firstOrFail();
    }

    private function admin(): User
    {
        $role = Role::factory()->create(['is_super_admin' => true]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_each_purchase_has_its_own_row_and_plan_count(): void
    {
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $plan = $this->plan('vip-month');
        $otherPlan = $this->plan('vip-other');

        $first = $this->buy($buyer, $plan);
        $second = $this->buy($buyer, $plan);
        $this->buy($otherBuyer, $otherPlan);
        $this->actingAs($this->admin());

        $this->get(route('admin.vip-plans.index'))
            ->assertOk()
            ->assertSee('مشاهده خریدها')
            ->assertViewHas('plans', fn ($plans) => $plans->firstWhere('id', $plan->id)->subscriptions_count === 2);
        $this->get(route('admin.vip-plans.index', ['sort' => 'purchases_count', 'direction' => 'desc']))
            ->assertViewHas('plans', fn ($plans) => $plans->first()->id === $plan->id);

        $this->get(route('admin.vip-plans.purchases.index', $plan))
            ->assertOk()
            ->assertSee('2 خرید ثبت‌شده')
            ->assertSee($buyer->email)
            ->assertSee($first->order->order_number)
            ->assertSee($second->order->order_number)
            ->assertViewHas('purchases', fn ($purchases) => $purchases->total() === 2
                && $purchases->pluck('user_id')->unique()->count() === 1
                && $purchases->pluck('id')->unique()->count() === 2);

        $this->assertSame($first->expires_at->toDateTimeString(), $second->starts_at->toDateTimeString());
        $this->get(route('admin.vip-plans.purchases.index', [$plan, 'status' => 'scheduled']))
            ->assertViewHas('purchases', fn ($purchases) => $purchases->total() === 1
                && $purchases->first()->id === $second->id);
        $this->get(route('admin.vip-plans.purchases.index', [$plan, 'order_number' => $first->order->order_number]))
            ->assertViewHas('purchases', fn ($purchases) => $purchases->total() === 1
                && $purchases->first()->id === $first->id);
        $this->get(route('admin.vip-plans.purchases.index', [$plan, 'user' => $otherBuyer->username]))
            ->assertViewHas('purchases', fn ($purchases) => $purchases->total() === 0);

        $firstDay = jalali_date($first->starts_at, 'Y/m/d H:i');
        $this->get(route('admin.vip-plans.purchases.index', [
            $plan, 'starts_at_from' => $firstDay, 'starts_at_to' => $firstDay,
        ]))->assertOk()->assertViewHas('purchases', fn ($purchases) => $purchases->total() === 1
            && $purchases->first()->id === $first->id);
        $dateOnly = jalali_date($first->starts_at, 'Y/m/d');
        $this->get(route('admin.vip-plans.purchases.index', [
            $plan, 'starts_at_from' => $dateOnly, 'starts_at_to' => $dateOnly,
        ]))->assertOk()
            ->assertSee('value="'.$dateOnly.'"', false)
            ->assertViewHas('purchases', fn ($purchases) => $purchases->total() === 1);

        app(RevokeVipSubscriptionsForOrderAction::class)->execute($first->order);
        $this->get(route('admin.vip-plans.purchases.index', [$plan, 'status' => 'revoked']))
            ->assertViewHas('purchases', fn ($purchases) => $purchases->total() === 1
                && $purchases->first()->id === $first->id);
        $this->get(route('admin.vip-plans.index'))
            ->assertViewHas('plans', fn ($plans) => $plans->firstWhere('id', $plan->id)->subscriptions_count === 2);

        $this->get(route('admin.vip-plans.purchases.index', [$plan, 'starts_at_from' => 'not-a-date']))
            ->assertSessionHasErrors('starts_at_from');
    }

    public function test_list_only_admin_can_view_purchase_history_but_guest_cannot(): void
    {
        $plan = $this->plan('vip-month');
        $this->get(route('admin.vip-plans.purchases.index', $plan))->assertRedirect();

        $role = Role::factory()->create();
        $role->permissions()->attach([
            Permission::query()->create(['group' => 'admin', 'ability' => 'display', 'label' => 'پنل'])->id,
            Permission::query()->create(['group' => 'vip-plan', 'ability' => 'list', 'label' => 'لیست'])->id,
        ]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $this->get(route('admin.vip-plans.index'))->assertOk()->assertSee('مشاهده خریدها');
        $this->get(route('admin.vip-plans.purchases.index', $plan))->assertOk();
    }
}
