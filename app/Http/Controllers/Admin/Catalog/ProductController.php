<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Application\Catalog\Actions\CreateProductAction;
use App\Application\Catalog\Actions\UpdateProductAction;
use App\Application\Taxonomy\Actions\GetAllTaxonomiesAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
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

        return view('admin.catalog.products.create', [
            'allCategories' => app(GetAllTaxonomiesAction::class)->execute(TaxonomyKeyEnum::PRODUCT_CATEGORY),
        ]);
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

        $selectedTags = $product->tags
            ->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->title,
            ])
            ->values()
            ->all();

        $allCategories = app(GetAllTaxonomiesAction::class)->execute(TaxonomyKeyEnum::PRODUCT_CATEGORY);
        $selectedCategoryIds = $product->categories()->pluck('taxonomy_terms.id')->all();

        return view('admin.catalog.products.edit', compact('product', 'allCategories', 'selectedCategoryIds', 'selectedTags'));
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
