<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProductInformationController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.catalog.product-information.index');
    }
}
