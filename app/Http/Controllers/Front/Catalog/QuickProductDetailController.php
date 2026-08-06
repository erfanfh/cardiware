<?php

namespace App\Http\Controllers\Front\Catalog;

use App\Domain\Catalog\Models\Product;
use Illuminate\Http\RedirectResponse;

class QuickProductDetailController
{
    public function __invoke(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        return redirect()->route('products.detail', $product);
    }
}
