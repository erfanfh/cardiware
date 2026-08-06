<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Domain\Marketplace\Models\Store;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StoreAnalyticsController extends Controller
{
    public function __invoke(Store $store): View
    {
        Gate::authorize('analytics.view');

        return view('admin.marketplace.stores.analytics', compact('store'));
    }
}
