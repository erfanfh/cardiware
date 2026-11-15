<?php

namespace App\Application\Marketplace\SellerRewards;

use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * رتبه‌بندی فروشگاه‌ها بر اساس فروش خالص در دوره.
 *
 * فروش خالص = جمع مبلغ نهایی اقلام سفارش‌هایی که پرداخت‌شده مانده‌اند (سفارش لغو یا
 * مرجوع‌شده حساب نمی‌شود). فقط اقلامی که سهم فروشنده دارند شمرده می‌شوند؛ محصولات
 * خودِ سایت (سهم فروشنده صفر) در رقابت فروشندگان نیستند.
 */
final class GetSellerRankingAction
{
    public function __construct(
        private readonly \App\Infrastructure\Services\Setting\Groups\SellerRewardSettings $settings,
    ) {
    }

    /** @return Collection<int, object{store_id: int, net_sales: int, orders_count: int, title: string, slug: string}> */
    public function execute(LeaderboardPeriod $period, int $limit = 20): Collection
    {
        $query = DB::table('order_items as i')
            ->join('orders as o', 'o.id', '=', 'i.order_id')
            ->join('stores as s', 's.id', '=', 'i.store_id')
            ->whereNull('i.parent_order_item_id')
            ->where('i.seller_commission_basis_points', '>', 0)
            ->where('o.status', OrderStatusEnum::PAID->value)
            ->where('s.status', StoreStatusEnum::ACTIVE->value)
            ->groupBy('i.store_id', 's.title', 's.slug')
            ->selectRaw('i.store_id, s.title, s.slug, SUM(i.final_total_amount) as net_sales, COUNT(DISTINCT i.order_id) as orders_count')
            ->havingRaw('SUM(i.final_total_amount) > 0')
            ->orderByDesc('net_sales')
            ->orderByDesc('orders_count')
            ->orderBy('i.store_id')
            ->limit($limit);

        // فروشگاه‌های مستثنی (معمولاً فروشگاه اصلی سایت) در رقابت شرکت نمی‌کنند تا
        // رتبه‌بندی بین فروشندگان واقعی بماند.
        $excludedStoreIds = $this->settings->excludedStoreIds();

        if ($excludedStoreIds !== []) {
            $query->whereNotIn('i.store_id', $excludedStoreIds);
        }

        if ($period->startsAt !== null) {
            $query->where('o.paid_at', '>=', $period->startsAt)->where('o.paid_at', '<', $period->endsAt);
        }

        return $query->get()->map(function (object $row): object {
            $row->store_id = (int) $row->store_id;
            $row->net_sales = (int) $row->net_sales;
            $row->orders_count = (int) $row->orders_count;

            return $row;
        });
    }
}
