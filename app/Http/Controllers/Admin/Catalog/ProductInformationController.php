<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductInformationController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('product.information');

        return view('admin.catalog.product-information.index');
    }
}
