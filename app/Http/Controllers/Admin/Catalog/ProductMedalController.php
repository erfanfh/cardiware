<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductMedalController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('product.medal');

        return view('admin.catalog.product-medals.index');
    }
}
