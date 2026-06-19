<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductAttributeController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('product.attribute');

        return view('admin.catalog.product-attributes.index');
    }
}
