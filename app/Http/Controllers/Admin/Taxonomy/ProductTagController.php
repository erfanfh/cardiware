<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ProductTagController extends Controller
{
    /**
     * Display a listing of tags.
     *
     * @return View
     */
    public function __invoke(): View
    {
        Gate::authorize('product.tag');

        return view('admin.taxonomy.product-tags.index');
    }
}
