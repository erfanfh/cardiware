<?php

namespace App\Http\Controllers\Front\Marketplace;

use App\Domain\Marketplace\Models\Store;
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

    public function show(Request $request, Store $store): View
    {
        abort_unless(
            $request->user()->stores()->whereKey($store->getKey())->exists(),
            403
        );

        return view('front.marketplace.seller.store-dashboard', compact('store'));
    }
}
