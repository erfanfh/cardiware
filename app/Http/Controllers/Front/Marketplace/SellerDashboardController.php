<?php

namespace App\Http\Controllers\Front\Marketplace;

use App\Application\Marketplace\Actions\GetSellerStoreReportAction;
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

        abort_if($stores->isEmpty(), 403);

        return view('front.marketplace.seller.dashboard', compact('stores'));
    }

    public function show(Request $request, Store $store, GetSellerStoreReportAction $reportAction): View
    {
        $days = (int) $request->query('days', 30);
        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }

        $report = $reportAction->execute($store, $days);
        $productsCount = Product::withoutGlobalScope(ActiveProductScope::class)->where('store_id', $store->id)->count();
        $commentsCount = Comment::query()
            ->where('commentable_type', (new Product())->getMorphClass())
            ->whereIn('commentable_id', Product::withoutGlobalScope(ActiveProductScope::class)->where('store_id', $store->id)->select('id'))
            ->count();
        $recentTickets = Ticket::query()
            ->whereIn('product_id', Product::withoutGlobalScope(ActiveProductScope::class)->where('store_id', $store->id)->select('id'))
            ->with('user')
            ->latestActivity()
            ->limit(4)
            ->get();

        return view('front.marketplace.seller.store-dashboard', compact('store', 'report', 'productsCount', 'commentsCount', 'recentTickets'));
    }
}
