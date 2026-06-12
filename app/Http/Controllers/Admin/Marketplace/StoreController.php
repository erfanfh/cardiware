<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Application\Catalog\Listings\ProductListing;
use App\Application\Marketplace\Actions\CreateStoreAction;
use App\Application\Marketplace\Actions\UpdateStoreAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Marketplace\Models\Store;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\IndexProductRequest;
use App\Http\Requests\Admin\Marketplace\UpdateStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param IndexProductRequest $request
     * @param ProductListing $productListing
     * @return View
     */
    public function index(IndexProductRequest $request, ProductListing $productListing): View
    {
        Gate::authorize('store.list');

//        $products = $productListing
//            ->handle(
//                Product::query()
//                    ->with([
//                        'store',
//                        'picture',
//                        'seoAnalysis',
//                        'categories',
//                        'tags',
//                        'saleItems',
//                    ])
//                    ->withCount('comments'),
//                $request->validated()
//            )
//            ->paginate($productListing->perPage())
//            ->withQueryString();
//
//        $statuses = collect(ProductStatusEnum::cases())
//            ->mapWithKeys(fn(ProductStatusEnum $status) => [
//                $status->value => $status->label(),
//            ])
//            ->all();
//
//        $types = collect(ProductTypeEnum::cases())
//            ->mapWithKeys(fn(ProductTypeEnum $status) => [
//                $status->value => $status->label(),
//            ])
//            ->all();
//
//        $structures = collect(ProductStructureEnum::cases())
//            ->mapWithKeys(fn(ProductStructureEnum $status) => [
//                $status->value => $status->label(),
//            ])
//            ->all();
//
//        $hasActiveFilters = $productListing->hasActiveFilters($request->validated());
//
//        $selectedTagIds = collect(request('tag_ids', []))
//            ->filter(fn($id) => filled($id))
//            ->map(fn($id) => (int)$id)
//            ->filter(fn($id) => $id > 0)
//            ->values();
//
//        $selectedTagOptions = TaxonomyTerm::query()
//            ->whereIn('id', $selectedTagIds)
//            ->get(['id', 'title'])
//            ->map(fn($tag) => [
//                'value' => (string)$tag->id,
//                'label' => $tag->title,
//            ])
//            ->values()
//            ->all();
//
//        $selectedCategoryIds = collect(request('category_ids', []))
//            ->filter(fn($id) => filled($id))
//            ->map(fn($id) => (int)$id)
//            ->filter(fn($id) => $id > 0)
//            ->values();
//
//        $selectedCategoryOptions = TaxonomyTerm::query()
//            ->whereIn('id', $selectedCategoryIds)
//            ->get(['id', 'title'])
//            ->map(fn($category) => [
//                'value' => (string)$category->id,
//                'label' => $category->title,
//            ])
//            ->values()
//            ->all();
//
//        $bulkActionOptions = DefaultBulkActions::set()->toOptions();
//
//        return view('admin.catalog.products.index', compact(
//            'products',
//            'statuses',
//            'types',
//            'structures',
//            'hasActiveFilters',
//            'selectedTagOptions',
//            'selectedCategoryOptions',
//            'bulkActionOptions',
//        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        Gate::authorize('store.create');

        return view('admin.marketplace.stores.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UpdateStoreRequest $request
     * @param CreateStoreAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(UpdateStoreRequest $request, CreateStoreAction $action): RedirectResponse
    {
        Gate::authorize('store.create');

        $store = $action->execute($request->validated());

        return redirect()->route('admin.stores.edit', $store)
            ->with('success', 'فروشنده با موفقیت ایجاد شد.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Store $store
     * @return View
     */
    public function edit(Store $store): View
    {
        Gate::authorize('store.edit');

        return view('admin.marketplace.stores.edit', compact('store'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     * @param UpdateStoreAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateStoreRequest $request, Store $store, UpdateStoreAction $action): RedirectResponse
    {
        Gate::authorize('store.edit');

        $action->execute($store, $request->validated());

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('success', 'فروشنده با موفقیت بروزرسانی شد.');
    }

    public function destroy(Product $product): RedirectResponse
    {
//        Gate::authorize('product.delete');
//
//        app(DeleteProductAction::class)->execute($product);
//
//        return redirect()
//            ->route('admin.products.index')
//            ->with('success', 'محصول با موفقیت حذف شد.');
    }
}
