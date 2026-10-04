<?php

namespace App\Application\Marketplace\Actions;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Marketplace\Enums\StoreWithdrawalStatusEnum;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Support\Facades\DB;

class GetStoreWithdrawalBalanceAction
{
    public function execute(Store $store): array
    {
        $earned = (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.store_id', $store->getKey())
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->sum('order_items.seller_commission_amount');

        $totals = $store->withdrawals()
            ->selectRaw('status, COALESCE(SUM(amount), 0) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $approved = (int) ($totals[StoreWithdrawalStatusEnum::APPROVED->value] ?? 0);
        $pending = (int) ($totals[StoreWithdrawalStatusEnum::PENDING->value] ?? 0);
        $balance = $earned - $approved;

        return [
            'earned' => $earned,
            'approved' => $approved,
            'pending' => $pending,
            'balance' => $balance,
            'available' => max(0, $balance - $pending),
        ];
    }
}
