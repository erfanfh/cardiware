<?php

namespace Tests\Feature\Commerce;

use App\Application\Commerce\Actions\Order\ChangeOrderStatusAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaidTransitionTimestampTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_new_paid_transition_replaces_stale_paid_at_with_transition_time(): void
    {
        $user = User::factory()->create();
        $oldPaidAt = now()->subDays(5);
        $transitionedAt = CarbonImmutable::parse('2026-08-17 12:34:56', config('app.timezone'));

        $order = Order::query()->create([
            'user_id' => $user->getKey(),
            'order_number' => 'TEST-REPAID-1',
            'status' => OrderStatusEnum::FAILED,
            'subtotal_amount' => 100000,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'paid_at' => $oldPaidAt,
        ]);

        $order = app(ChangeOrderStatusAction::class)->execute(
            $order,
            OrderStatusEnum::PAID,
            $transitionedAt,
        );

        $this->assertSame(
            $transitionedAt->format('Y-m-d H:i:s'),
            $order->paid_at?->format('Y-m-d H:i:s'),
        );
    }
}
