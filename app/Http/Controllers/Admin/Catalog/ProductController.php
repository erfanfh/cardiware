<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Application\Catalog\Actions\CreateProductAction;
use App\Application\Catalog\Actions\UpdateProductAction;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        Gate::authorize('product.create');

        return view('admin.catalog.products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UpdateProductRequest $request
     * @param CreateProductAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(UpdateProductRequest $request, CreateProductAction $action): RedirectResponse
    {
        Gate::authorize('product.create');

        $product = $action->execute($request->validated());

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'محصول با موفقیت ایجاد شد.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Product $product
     * @return View
     */
    public function edit(Product $product): View
    {
        Gate::authorize('product.edit');

        return view('admin.catalog.products.edit', compact('product'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @param UpdateProductAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): RedirectResponse
    {
        Gate::authorize('product.edit');

        $action->execute($product, $request->validated());

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'محصول با موفقیت بروزرسانی شد.');
    }
}
