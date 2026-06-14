<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Admin\AdminSearchController;

class ProductSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return Product::class;
    }

    protected function permissionName(): string
    {
        return 'product.list';
    }
}
