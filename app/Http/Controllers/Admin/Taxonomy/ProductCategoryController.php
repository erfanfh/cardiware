<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @return View
     */
    public function __invoke(): View
    {
        Gate::authorize('product.category');

        return view('admin.taxonomy.product-categories.index');
    }
}
