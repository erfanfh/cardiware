<?php

namespace App\Http\Controllers\Front\Marketplace;

use App\Application\Marketplace\Actions\GetSellerStoreReportAction;
use App\Application\Marketplace\Actions\GetStoreWithdrawalBalanceAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Scopes\ActiveProductScope;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stores = $request->user()->stores()
            ->orderBy('stores.title')
            ->get();

        $stores = $stores->filter(fn (Store $store) => $store->sellerCan('access_panel'));

        abort_if($stores->isEmpty(), 403);

        return view('front.marketplace.seller.dashboard', compact('stores'));
    }

    public function show(Request $request, Store $store, GetSellerStoreReportAction $reportAction, GetStoreWithdrawalBalanceAction $balanceAction): View
    {
        $days = (int) $request->query('days', 30);
        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }

        $report = $reportAction->execute($store, $days);
        $withdrawalBalance = $store->sellerCan('manage_withdrawals') ? $balanceAction->execute($store) : null;
        $productsCount = Product::withoutGlobalScope(ActiveProductScope::class)->where('store_id', $store->id)->count();
        $commentsCount = $store->sellerCan('view_comments') ? Comment::query()
            ->where('commentable_type', (new Product)->getMorphClass())
            ->whereIn('commentable_id', Product::withoutGlobalScope(ActiveProductScope::class)->where('store_id', $store->id)->select('id'))
            ->count() : null;
        $recentTickets = $store->sellerCan('view_tickets') ? Ticket::query()
            ->whereIn('product_id', Product::withoutGlobalScope(ActiveProductScope::class)->where('store_id', $store->id)->select('id'))
            ->with('user')
            ->latestActivity()
            ->limit(4)
            ->get() : collect();

        return view('front.marketplace.seller.store-dashboard', compact('store', 'report', 'withdrawalBalance', 'productsCount', 'commentsCount', 'recentTickets'));
    }
}
