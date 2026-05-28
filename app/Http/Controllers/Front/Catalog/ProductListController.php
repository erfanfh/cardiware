<?php

namespace App\Http\Controllers\Front\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProductListController extends Controller
{
    public function __invoke(): View
    {
        return view('front.catalog.products.list');
    }
}
