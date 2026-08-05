<?php

namespace App\Application\Commerce\Analytics\Actions;

use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsMetricDTO;
use App\Application\Commerce\Analytics\DTOs\CommerceOverviewDTO;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Enums\WalletTopUpPurposeEnum;
use App\Domain\Financial\Models\WalletTopUp;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class GetCommerceOverviewAction
{
    public function execute(AnalyticsFilterDTO $filter): CommerceOverviewDTO
    {
        return Cache::remember(
            $this->cacheKey($filter),
            now()->addSeconds($filter->preset->cacheTtlSeconds()),
            fn(): CommerceOverviewDTO => $this->buildOverview($filter),
        );
    }

    public function forget(AnalyticsFilterDTO $filter): void
    {
        Cache::forget($this->cacheKey($filter));
    }

    private function buildOverview(AnalyticsFilterDTO $filter): CommerceOverviewDTO
    {
        $current = $this->summary($filter->range);
        $previous = $filter->comparisonRange
            ? $this->summary($filter->comparisonRange)
            : null;

        return new CommerceOverviewDTO(
            filter: $filter,
            metrics: $this->buildMetrics($current, $previous),
            salesTrend: $this->salesTrend($filter->range),
            gatewayPerformance: $this->gatewayPerformance($filter->range),
            topProducts: $this->topProducts($filter->range),
            generatedAt: now()->toImmutable(),
        );
    }

    private function cacheKey(AnalyticsFilterDTO $filter): string
    {
        return 'commerce-analytics:overview:v1:' . $filter->cacheKey();
    }

    /**
     * @return array<string, int|float>
     */
    private function summary(AnalyticsDateRangeDTO $range): array
    {
        $orders = DB::table('orders')
            ->where('status', OrderStatusEnum::PAID->value)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$range->from, $range->to])
            ->selectRaw('COUNT(*) as paid_orders_count')
            ->selectRaw('COALESCE(SUM(subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(coupon_discount_amount), 0) as coupon_discount_amount')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->selectRaw('COUNT(DISTINCT user_id) as unique_customers')
            ->first();

        $items = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->whereNotNull('orders.paid_at')
            ->whereBetween('orders.paid_at', [$range->from, $range->to])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) as product_units_sold',
                [$this->morphType(ProductSaleItem::class)],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) as addons_sold',
                [$this->morphType(ProductAddon::class)],
            )
            ->first();

        $paymentAttempts = $this->externalPayments()
            ->whereBetween('payments.created_at', [$range->from, $range->to])
            ->selectRaw('COUNT(*) as attempts_count')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payments.status = ? THEN 1 ELSE 0 END), 0) as successful_count',
                [PaymentStatusEnum::PAID->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payments.status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as unsuccessful_count',
                [
                    PaymentStatusEnum::FAILED->value,
                    PaymentStatusEnum::CANCELLED->value,
                    PaymentStatusEnum::EXPIRED->value,
                ],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payments.status IN (?, ?) THEN 1 ELSE 0 END), 0) as open_count',
                [
                    PaymentStatusEnum::INITIATED->value,
                    PaymentStatusEnum::PENDING->value,
                ],
            )
            ->first();

        $cash = $this->externalPayments()
            ->where('payments.status', PaymentStatusEnum::PAID->value)
            ->whereNotNull('payments.paid_at')
            ->whereBetween('payments.paid_at', [$range->from, $range->to])
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as external_cash_inflow')
            ->first();

        $salesCashInflow = $this->salesRelatedExternalPayments()
            ->where('payments.status', PaymentStatusEnum::PAID->value)
            ->whereNotNull('payments.paid_at')
            ->whereBetween('payments.paid_at', [$range->from, $range->to])
            ->sum('payments.amount');

        $manualTopUpCashInflow = $this->manualWalletTopUpExternalPayments()
            ->where('payments.status', PaymentStatusEnum::PAID->value)
            ->whereNotNull('payments.paid_at')
            ->whereBetween('payments.paid_at', [$range->from, $range->to])
            ->sum('payments.amount');

        $paidOrdersCount = (int)($orders->paid_orders_count ?? 0);
        $netSales = (int)($orders->net_sales ?? 0);
        $attemptsCount = (int)($paymentAttempts->attempts_count ?? 0);
        $successfulCount = (int)($paymentAttempts->successful_count ?? 0);

        return [
            'gross_sales' => (int)($orders->gross_sales ?? 0),
            'product_discount_amount' => max(
                0,
                (int)($orders->discount_amount ?? 0)
                - (int)($orders->coupon_discount_amount ?? 0),
            ),
            'coupon_discount_amount' => (int)($orders->coupon_discount_amount ?? 0),
            'discount_amount' => (int)($orders->discount_amount ?? 0),
            'net_sales' => $netSales,
            'paid_orders_count' => $paidOrdersCount,
            'average_order_value' => $paidOrdersCount > 0
                ? (int)round($netSales / $paidOrdersCount)
                : 0,
            'unique_customers' => (int)($orders->unique_customers ?? 0),
            'product_units_sold' => (int)($items->product_units_sold ?? 0),
            'addons_sold' => (int)($items->addons_sold ?? 0),
            'external_payment_attempts' => $attemptsCount,
            'successful_external_payments' => $successfulCount,
            'unsuccessful_external_payments' => (int)($paymentAttempts->unsuccessful_count ?? 0),
            'open_external_payments' => (int)($paymentAttempts->open_count ?? 0),
            'external_payment_success_rate' => $attemptsCount > 0
                ? round(($successfulCount / $attemptsCount) * 100, 2)
                : 0.0,
            'external_cash_inflow' => (int)($cash->external_cash_inflow ?? 0),
            'sales_external_cash_inflow' => (int)$salesCashInflow,
            'manual_wallet_top_up_cash_inflow' => (int)$manualTopUpCashInflow,
        ];
    }

    /**
     * @param array<string, int|float> $current
     * @param array<string, int|float>|null $previous
     * @return array<string, AnalyticsMetricDTO>
     */
    private function buildMetrics(array $current, ?array $previous): array
    {
        return collect($current)
            ->mapWithKeys(function (int|float $value, string $key) use ($previous): array {
                return [
                    $key => AnalyticsMetricDTO::compare(
                        current: $value,
                        previous: $previous[$key] ?? null,
                    ),
                ];
            })
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function salesTrend(AnalyticsDateRangeDTO $range): Collection
    {
        $rows = DB::table('orders')
            ->where('status', OrderStatusEnum::PAID->value)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$range->from, $range->to])
            ->groupByRaw('DATE(paid_at)')
            ->orderByRaw('DATE(paid_at)')
            ->selectRaw('DATE(paid_at) as bucket')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->get()
            ->keyBy('bucket');

        $cursor = $range->from->startOfDay();
        $lastDay = $range->to->startOfDay();
        $result = collect();

        while ($cursor->lessThanOrEqualTo($lastDay)) {
            $key = $cursor->format('Y-m-d');
            $row = $rows->get($key);

            $result->push([
                'date' => $key,
                'label' => Jalalian::fromCarbon($cursor->toMutable())->format('m/d'),
                'orders_count' => (int)($row->orders_count ?? 0),
                'gross_sales' => (int)($row->gross_sales ?? 0),
                'discount_amount' => (int)($row->discount_amount ?? 0),
                'net_sales' => (int)($row->net_sales ?? 0),
            ]);

            $cursor = $cursor->addDay();
        }

        return $result;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function gatewayPerformance(AnalyticsDateRangeDTO $range): Collection
    {
        $rows = $this->externalPayments()
            ->whereBetween('payments.created_at', [$range->from, $range->to])
            ->groupBy('payments.gateway')
            ->select('payments.gateway')
            ->selectRaw('COUNT(*) as attempts_count')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payments.status = ? THEN 1 ELSE 0 END), 0) as successful_count',
                [PaymentStatusEnum::PAID->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payments.status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as unsuccessful_count',
                [
                    PaymentStatusEnum::FAILED->value,
                    PaymentStatusEnum::CANCELLED->value,
                    PaymentStatusEnum::EXPIRED->value,
                ],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payments.status IN (?, ?) THEN 1 ELSE 0 END), 0) as open_count',
                [
                    PaymentStatusEnum::INITIATED->value,
                    PaymentStatusEnum::PENDING->value,
                ],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payments.status = ? THEN payments.amount ELSE 0 END), 0) as paid_amount',
                [PaymentStatusEnum::PAID->value],
            )
            ->orderByDesc('paid_amount')
            ->get();

        return $rows->map(function (object $row): array {
            $gateway = PaymentGatewayEnum::tryFrom((string)$row->gateway);
            $attempts = (int)$row->attempts_count;
            $successful = (int)$row->successful_count;

            return [
                'gateway' => (string)$row->gateway,
                'label' => $gateway?->label() ?? (string)$row->gateway,
                'attempts_count' => $attempts,
                'successful_count' => $successful,
                'unsuccessful_count' => (int)$row->unsuccessful_count,
                'open_count' => (int)$row->open_count,
                'success_rate' => $attempts > 0
                    ? round(($successful / $attempts) * 100, 2)
                    : 0.0,
                'paid_amount' => (int)$row->paid_amount,
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function topProducts(AnalyticsDateRangeDTO $range): Collection
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->whereNotNull('orders.paid_at')
            ->whereBetween('orders.paid_at', [$range->from, $range->to])
            ->whereNotNull('order_items.product_id')
            ->groupBy('order_items.product_id')
            ->select('order_items.product_id')
            ->selectRaw('COALESCE(SUM(order_items.subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(order_items.discount_amount + order_items.coupon_discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) as net_sales')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) as sold_quantity',
                [$this->morphType(ProductSaleItem::class)],
            )
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->orderByDesc('net_sales')
            ->limit(8)
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $productIds = $rows
            ->pluck('product_id')
            ->map(static fn(mixed $id): int => (int)$id)
            ->all();

        $products = Product::query()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->whereIn('id', $productIds)
            ->pluck('title', 'id');

        $latestSnapshotItemIds = DB::table('order_items')
            ->whereIn('product_id', $productIds)
            ->whereNotNull('snapshot')
            ->groupBy('product_id')
            ->selectRaw('MAX(id) as id')
            ->pluck('id');

        $snapshotTitles = DB::table('order_items')
            ->whereIn('id', $latestSnapshotItemIds)
            ->get(['product_id', 'snapshot'])
            ->mapWithKeys(function (object $item): array {
                $snapshot = is_string($item->snapshot)
                    ? json_decode($item->snapshot, true)
                    : (array)$item->snapshot;

                return [
                    (int)$item->product_id => data_get($snapshot, 'product_title')
                        ?? data_get($snapshot, 'title'),
                ];
            });

        return $rows->map(function (object $row) use ($products, $snapshotTitles): array {
            $productId = (int)$row->product_id;

            return [
                'product_id' => $productId,
                'title' => $products->get($productId)
                    ?? $snapshotTitles->get($productId)
                        ?? 'محصول حذف شده',
                'gross_sales' => (int)$row->gross_sales,
                'discount_amount' => (int)$row->discount_amount,
                'net_sales' => (int)$row->net_sales,
                'sold_quantity' => (int)$row->sold_quantity,
                'orders_count' => (int)$row->orders_count,
            ];
        });
    }

    private function externalPayments(): Builder
    {
        return DB::table('payments')
            ->whereIn(
                'payments.gateway',
                collect(PaymentGatewayEnum::cases())
                    ->filter(static fn(PaymentGatewayEnum $gateway): bool => $gateway->isExternal())
                    ->map(static fn(PaymentGatewayEnum $gateway): string => $gateway->value)
                    ->all(),
            );
    }

    private function salesRelatedExternalPayments(): Builder
    {
        $orderMorphType = $this->morphType(Order::class);
        $walletTopUpMorphType = $this->morphType(WalletTopUp::class);

        return $this->externalPayments()
            ->where(function (Builder $query) use ($orderMorphType, $walletTopUpMorphType): void {
                $query
                    ->where('payments.payable_type', $orderMorphType)
                    ->orWhere(function (Builder $query) use ($walletTopUpMorphType): void {
                        $query
                            ->where('payments.payable_type', $walletTopUpMorphType)
                            ->whereExists(function (Builder $exists): void {
                                $exists
                                    ->selectRaw('1')
                                    ->from('wallet_top_ups')
                                    ->whereColumn('wallet_top_ups.id', 'payments.payable_id')
                                    ->where(
                                        'wallet_top_ups.purpose',
                                        WalletTopUpPurposeEnum::ORDER_PAYMENT->value,
                                    );
                            });
                    });
            });
    }

    private function manualWalletTopUpExternalPayments(): Builder
    {
        return $this->externalPayments()
            ->where('payments.payable_type', $this->morphType(WalletTopUp::class))
            ->whereExists(function (Builder $exists): void {
                $exists
                    ->selectRaw('1')
                    ->from('wallet_top_ups')
                    ->whereColumn('wallet_top_ups.id', 'payments.payable_id')
                    ->where(
                        'wallet_top_ups.purpose',
                        WalletTopUpPurposeEnum::MANUAL->value,
                    );
            });
    }

    private function morphType(string $class): string
    {
        return (string)Relation::getMorphAlias($class);
    }
}
