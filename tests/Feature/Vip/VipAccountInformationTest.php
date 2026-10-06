<?php

namespace Tests\Feature\Vip;

use App\Application\Commerce\Actions\Checkout\CompleteOrderAfterSuccessfulPaymentAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use App\Http\Middleware\ForceTrailingSlash;
use App\Livewire\Front\Account\AccountInformationSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VipAccountInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_information_tracks_vip_activation_and_expiration(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(AccountInformationSection::class)
            ->assertSee('کاربر عادی')
            ->assertSee('هنوز اشتراک VIP فعالی ندارید')
            ->assertSee('مشاهده پلن‌های VIP')
            ->assertSee(route('vip.index'));
        $this->actingAs($user)
            ->withoutMiddleware(ForceTrailingSlash::class)
            ->get(route('account.information'))
            ->assertOk()
            ->assertSee('هنوز اشتراک VIP فعالی ندارید');

        $plan = VipPlan::query()->create([
            'code' => 'vip-account-info',
            'title' => 'پلن ماهانه',
            'duration_days' => 30,
            'price_toman' => 100000,
            'is_active' => true,
        ]);
        $this->actingAs($user)->post(route('vip.cart.add', $plan));
        $order = app(CreateOrderFromCartAction::class)->execute(Cart::query()->active()->firstOrFail());
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($order);
        $subscription = VipSubscription::query()->where('order_id', $order->id)->firstOrFail();

        Livewire::actingAs($user)->test(AccountInformationSection::class)
            ->assertSee('کاربر VIP')
            ->assertSee('اشتراک VIP شما فعال است')
            ->assertSee(jalali_date($subscription->expires_at, 'j F Y').' ساعت '.jalali_date($subscription->expires_at, 'H:i'))
            ->assertDontSee('هنوز اشتراک VIP فعالی ندارید')
            ->assertDontSee('مشاهده پلن‌های VIP');

        $subscription->update(['expires_at' => now()->subSecond()]);
        Livewire::actingAs($user)->test(AccountInformationSection::class)
            ->assertSee('کاربر عادی')
            ->assertSee('مشاهده پلن‌های VIP');
    }
}
