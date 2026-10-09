<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Application\Catalog\Actions\CreateProductAction;
use App\Application\Catalog\Actions\DeleteProductAction;
use App\Application\Catalog\Actions\UpdateProductAction;
use App\Application\Catalog\Listings\ProductListing;
use App\Application\Catalog\Support\ProductEditLock;
use App\Application\Taxonomy\Actions\GetAllTaxonomiesAction;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\IndexProductRequest;
use App\Http\Requests\Admin\Catalog\UpdateProductRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
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
        Gate::authorize('product.list');

        $products = $productListing
            ->handle(
                Product::query()
                    ->with([
                        'store',
                        'picture',
                        'seoAnalysis',
                        'categories',
                        'tags',
                        'saleItems',
                        'editLockUser',
                    ])
                    ->withCount('comments'),
                $request->validated()
            )
            ->paginate($productListing->perPage())
            ->withQueryString();

        $statuses = collect(ProductStatusEnum::cases())
            ->mapWithKeys(fn(ProductStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $types = collect(ProductTypeEnum::cases())
            ->mapWithKeys(fn(ProductTypeEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $structures = collect(ProductStructureEnum::cases())
            ->mapWithKeys(fn(ProductStructureEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $hasActiveFilters = $productListing->hasActiveFilters($request->validated());

        $selectedTagIds = collect(request('tag_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values();

        $selectedTagOptions = TaxonomyTerm::query()
            ->whereIn('id', $selectedTagIds)
            ->get(['id', 'title'])
            ->map(fn($tag) => [
                'value' => (string)$tag->id,
                'label' => $tag->title,
            ])
            ->values()
            ->all();

        $selectedCategoryIds = collect(request('category_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values();

        $selectedCategoryOptions = TaxonomyTerm::query()
            ->whereIn('id', $selectedCategoryIds)
            ->get(['id', 'title'])
            ->map(fn($category) => [
                'value' => (string)$category->id,
                'label' => $category->title,
            ])
            ->values()
            ->all();

        $bulkActionOptions = DefaultBulkActions::set()->toOptions();

        return view('admin.catalog.products.index', compact(
            'products',
            'statuses',
            'types',
            'structures',
            'hasActiveFilters',
            'selectedTagOptions',
            'selectedCategoryOptions',
            'bulkActionOptions',
        ));
    }

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
     * @return Response
     */
    public function edit(Request $request, Product $product, ProductEditLock $locks): Response
    {
        Gate::authorize('product.edit');

        $previous = $request->session()->get('product_edit_lock');
        $token = $request->old('edit_lock_token');
        if (! is_string($token) && is_array($previous) && ($previous['product_id'] ?? null) === $product->id) {
            $token = $previous['token'] ?? null;
        }
        $editLockToken = $locks->acquire($product, $request->user(), is_string($token) ? $token : null);

        $product->load([
            'informationEntries',
            'imageGallery.mediaFile.primaryVariant',
            'saleItems.launcherMods.image.primaryVariant',
            'saleItems.launcherMods.group',
            'saleItems.launcherMods.files',
            'saleItems.launcherModGroups',
            'saleItems.launcherUpdates.files',
        ]);

        $selectedTags = $product->tags
            ->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->title,
            ])
            ->values()
            ->all();

        $allCategories = app(GetAllTaxonomiesAction::class)->execute(TaxonomyKeyEnum::PRODUCT_CATEGORY);
        $selectedCategoryIds = $product->categories()->pluck('taxonomy_terms.id')->all();

        return response()->view('admin.catalog.products.edit', compact('product', 'allCategories', 'selectedCategoryIds', 'selectedTags', 'editLockToken'))
            ->header('Cache-Control', 'no-store');
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
    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action, ProductEditLock $locks): RedirectResponse
    {
        Gate::authorize('product.edit');

        $token = $request->input('edit_lock_token');
        $locks->save($product, $request->user(), is_string($token) ? $token : '',
            fn () => $action->execute($product, $request->validated()));

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('product_edit_lock', ['product_id' => $product->id, 'token' => $token])
            ->with('success', 'محصول با موفقیت بروزرسانی شد.');
    }

    public function destroy(Product $product, ProductEditLock $locks): RedirectResponse
    {
        Gate::authorize('product.delete');

        $locks->withoutEditor($product, fn () => app(DeleteProductAction::class)->execute($product));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول با موفقیت حذف شد.');
    }
}
