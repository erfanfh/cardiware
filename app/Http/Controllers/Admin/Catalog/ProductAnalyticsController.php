<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductAnalyticsController extends Controller
{
    public function __invoke(Product $product): View
    {
        Gate::authorize('analytics.view');

        return view('admin.catalog.products.analytics', compact('product'));
    }
}
