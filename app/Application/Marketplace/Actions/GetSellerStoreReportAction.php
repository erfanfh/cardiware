<?php

namespace App\Application\Marketplace\Actions;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Marketplace\Models\Store;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class GetSellerStoreReportAction
{
    public function execute(Store $store, int $days): array
    {
        abort_unless($store->sellerCan('access_panel'), 403);
        $start = CarbonImmutable::now()->startOfDay()->subDays($days - 1);
        $end = CarbonImmutable::now()->endOfDay();

        $base = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.store_id', $store->getKey())
            ->where('orders.status', OrderStatusEnum::PAID->value);

        $lifetime = (clone $base)
            ->selectRaw('COUNT(DISTINCT orders.id) AS orders_count')
            ->selectRaw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_amount')
            ->first();

        $base->whereBetween('orders.paid_at', [$start, $end]);

        $summary = (clone $base)->selectRaw('COUNT(DISTINCT orders.id) AS orders_count')
            ->selectRaw('SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END) AS units_count', ['product_sale_item'])
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) AS gross_amount')
            ->selectRaw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_amount')
            ->selectRaw('COALESCE(SUM(COALESCE(order_items.platform_commission_amount, order_items.final_total_amount)), 0) AS platform_amount')
            ->first();

        $daily = (clone $base)
            ->selectRaw('DATE(orders.paid_at) AS sale_day')
            ->selectRaw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_amount')
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) AS gross_amount')
            ->groupByRaw('DATE(orders.paid_at)')
            ->pluck('seller_amount', 'sale_day');

        $topProducts = (clone $base)
            ->join('products', function ($join) use ($store): void {
                $join->on('products.id', '=', 'order_items.product_id')
                    ->where('products.store_id', '=', $store->getKey())
                    ->whereNull('products.deleted_at');
            })
            ->select('products.id', 'products.title')
            ->selectRaw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_amount')
            ->selectRaw('SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END) AS units_count', ['product_sale_item'])
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('seller_amount')
            ->limit(5)
            ->get();

        $labels = [];
        $amounts = [];
        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            $labels[] = $day->format('m/d');
            $amounts[] = (int) ($daily[$day->toDateString()] ?? 0);
        }

        $report = [
            'days' => $days,
            'summary' => [
                'orders' => (int) ($summary->orders_count ?? 0),
                'units' => (int) ($summary->units_count ?? 0),
                'gross' => (int) ($summary->gross_amount ?? 0),
                'seller' => (int) ($summary->seller_amount ?? 0),
                'platform' => (int) ($summary->platform_amount ?? 0),
            ],
            'chart' => ['labels' => $labels, 'values' => $amounts],
            'top_products' => $topProducts,
            'lifetime' => [
                'orders' => (int) ($lifetime->orders_count ?? 0),
                'seller' => (int) ($lifetime->seller_amount ?? 0),
            ],
        ];

        if (! $store->sellerCan('view_sales_count')) {
            $report['summary']['orders'] = $report['summary']['units'] = null;
            $report['lifetime']['orders'] = null;
            $report['top_products']->each(function ($product): void {
                unset($product->units_count);
            });
        }

        if (! $store->sellerCan('view_commission')) {
            $report['summary']['platform'] = null;
        }

        if (! $store->sellerCan('view_revenue')) {
            foreach (['gross', 'seller', 'platform'] as $field) {
                $report['summary'][$field] = null;
            }
            $report['lifetime']['seller'] = null;
            $report['chart'] = null;
            $report['top_products'] = collect();
        }

        return $report;
    }
}
