<?php

namespace Tests\Feature\Commerce;

use App\Application\Commerce\Analytics\Actions\GetCommerceOverviewAction;
use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\Enums\AnalyticsComparisonEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsGranularityEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsRangePresetEnum;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Identity\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlatformRevenueAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_split_uses_paid_item_snapshots_in_the_selected_period(): void
    {
        $buyer = User::factory()->create();

        $currentOrder = $this->order($buyer, 'CURRENT', OrderStatusEnum::PAID, '2026-10-04 12:00:00', 100000);
        $this->item($currentOrder, 'product_sale_item', 70000, 20000, 50000);
        $this->item($currentOrder, 'product_addon', 30000, 15000, 15000);

        $legacyOrder = $this->order($buyer, 'LEGACY', OrderStatusEnum::PAID, '2026-10-05 10:00:00', 40000);
        $this->item($legacyOrder, 'product_sale_item', 40000, null, null);

        $refundedOrder = $this->order($buyer, 'REFUNDED', OrderStatusEnum::REFUNDED, '2026-10-04 10:00:00', 20000);
        $this->item($refundedOrder, 'product_sale_item', 20000, 10000, 10000);

        $previousOrder = $this->order($buyer, 'PREVIOUS', OrderStatusEnum::PAID, '2026-10-01 10:00:00', 10000);
        $this->item($previousOrder, 'product_sale_item', 10000, 2500, 7500);

        $currentRange = new AnalyticsDateRangeDTO(
            CarbonImmutable::parse('2026-10-03 00:00:00'),
            CarbonImmutable::parse('2026-10-05 23:59:59'),
            'current',
        );
        $previousRange = new AnalyticsDateRangeDTO(
            CarbonImmutable::parse('2026-09-30 00:00:00'),
            CarbonImmutable::parse('2026-10-02 23:59:59'),
            'previous',
        );
        $filter = new AnalyticsFilterDTO(
            AnalyticsRangePresetEnum::CUSTOM,
            $currentRange,
            AnalyticsComparisonEnum::PREVIOUS_PERIOD,
            $previousRange,
            AnalyticsGranularityEnum::DAY,
            AnalyticsGranularityEnum::DAY,
        );

        $metrics = app(GetCommerceOverviewAction::class)->execute($filter)->metrics;

        $this->assertSame(140000, $metrics['commissionable_sales']->value);
        $this->assertSame(35000, $metrics['seller_share']->value);
        $this->assertSame(105000, $metrics['platform_net_revenue']->value);
        $this->assertSame(
            $metrics['commissionable_sales']->value,
            $metrics['seller_share']->value + $metrics['platform_net_revenue']->value,
        );
        $this->assertSame(2500, $metrics['seller_share']->comparisonValue);
        $this->assertSame(7500, $metrics['platform_net_revenue']->comparisonValue);
    }

    private function order(User $buyer, string $number, OrderStatusEnum $status, string $paidAt, int $amount): int
    {
        return DB::table('orders')->insertGetId([
            'user_id' => $buyer->id,
            'order_number' => 'ANALYTICS-'.$number,
            'status' => $status->value,
            'subtotal_amount' => $amount,
            'taxable_amount' => $amount,
            'total_amount' => $amount,
            'paid_at' => $paidAt,
            'created_at' => $paidAt,
            'updated_at' => $paidAt,
        ]);
    }

    private function item(int $orderId, string $type, int $amount, ?int $seller, ?int $platform): void
    {
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'purchasable_type' => $type,
            'purchasable_id' => 1,
            'quantity' => 1,
            'unit_price' => $amount,
            'subtotal_amount' => $amount,
            'total_amount' => $amount,
            'final_total_amount' => $amount,
            'seller_commission_amount' => $seller,
            'platform_commission_amount' => $platform,
        ]);
    }
}
