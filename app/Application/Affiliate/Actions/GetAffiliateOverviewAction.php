<?php

namespace App\Application\Affiliate\Actions;

use App\Domain\Affiliate\Enums\AffiliateOrderStatusEnum;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatusEnum;
use App\Domain\Affiliate\Models\Affiliate;
use Illuminate\Support\Facades\DB;

/**
 * آمار کامل یک همکار: کلیک، ثبت‌نام، خریدار، سفارش، درآمد، موجودی.
 *
 * @return array<string, mixed>
 */
class GetAffiliateOverviewAction
{
    /** @return array<string, mixed> */
    public function execute(Affiliate $affiliate): array
    {
        $affiliateId = $affiliate->getKey();

        $clicksCount = (int) DB::table('affiliate_clicks')
            ->where('affiliate_id', $affiliateId)
            ->count();

        $signupsCount = (int) DB::table('affiliate_referrals')
            ->where('affiliate_id', $affiliateId)
            ->count();

        $ordersBase = DB::table('affiliate_orders')->where('affiliate_id', $affiliateId);

        $ordersCount = (clone $ordersBase)->count();
        $buyersCount = (clone $ordersBase)->distinct('user_id')->count('user_id');

        $activeOrders = (clone $ordersBase)->where('status', AffiliateOrderStatusEnum::ACTIVE->value);
        $revenue = (int) (clone $activeOrders)->sum('order_amount');
        $totalCommission = (int) (clone $activeOrders)->sum('commission_amount');
        $revertedCommission = (int) (clone $ordersBase)
            ->where('status', AffiliateOrderStatusEnum::REVERTED->value)
            ->sum('commission_amount');

        $withdrawalsBase = DB::table('affiliate_withdrawals')->where('affiliate_id', $affiliateId);
        $paidOut = (int) (clone $withdrawalsBase)
            ->where('status', AffiliateWithdrawalStatusEnum::APPROVED->value)
            ->sum('amount');
        $pendingWithdrawalAmount = (int) (clone $withdrawalsBase)
            ->where('status', AffiliateWithdrawalStatusEnum::PENDING->value)
            ->sum('amount');

        // موجودی قابل برداشت = کمیسیون فعال − پرداخت‌شده − در انتظار بررسی
        $availableBalance = max(0, $totalCommission - $paidOut - $pendingWithdrawalAmount);

        $recentOrders = $affiliate->orders()
            ->with(['user:id,first_name,last_name', 'order:id,order_number,total_amount,paid_at'])
            ->latest('id')
            ->limit(10)
            ->get();

        $withdrawals = $affiliate->withdrawals()
            ->latest('id')
            ->limit(10)
            ->get();

        return [
            'affiliate' => $affiliate,
            'clicksCount' => $clicksCount,
            'signupsCount' => $signupsCount,
            'buyersCount' => $buyersCount,
            'ordersCount' => $ordersCount,
            'conversionPercent' => $clicksCount > 0
                ? round($buyersCount * 100 / $clicksCount, 1)
                : 0.0,
            'revenue' => $revenue,
            'totalCommission' => $totalCommission,
            'revertedCommission' => $revertedCommission,
            'paidOut' => $paidOut,
            'pendingWithdrawalAmount' => $pendingWithdrawalAmount,
            'availableBalance' => $availableBalance,
            'lifetimeCommission' => $totalCommission + $revertedCommission,
            'recentOrders' => $recentOrders,
            'withdrawals' => $withdrawals,
        ];
    }
}
