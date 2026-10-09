<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Application\Catalog\Support\ProductEditLock;
use App\Domain\Catalog\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProductEditLockController
{
    public function renew(Request $request, Product $product, ProductEditLock $locks): Response
    {
        Gate::authorize('product.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->renew($product, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }

    public function release(Request $request, Product $product, ProductEditLock $locks): Response
    {
        Gate::authorize('product.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->release($product, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }
}
