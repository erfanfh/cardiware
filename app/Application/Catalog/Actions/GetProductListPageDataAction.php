<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetProductListPageDataAction
{
    public function execute(Request $request, int $page = 1, int $perPage = 12): array
    {
        [$availablePriceMin, $availablePriceMax] = $this->availablePriceRange();

        $priceMin = (int)$request->query('priceMin', $availablePriceMin);
        $priceMax = (int)$request->query('priceMax', $availablePriceMax);

        $priceMin = max($priceMin, $availablePriceMin);
        $priceMax = min($priceMax, $availablePriceMax);

        if ($priceMin > $priceMax) {
            [$priceMin, $priceMax] = [$priceMax, $priceMin];
        }

        $selectedCategories = collect((array)$request->query('categories', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        $sort = $request->query('sort', 'latest');

        $query = Product::query()
            ->with([
                'picture',
                'categories',
                'saleItems:id,product_id,base_price,status,sort_order',
                'store:id,title,slug',
            ])
            ->withMin('saleItems as min_price', 'base_price')
            ->withMax('saleItems as max_price', 'base_price');

        $this->applyPublishedScope($query);

        $query->whereHas('saleItems', function (Builder $query) use ($priceMin, $priceMax): void {
            $query
                ->where('base_price', '>=', $priceMin)
                ->where('base_price', '<=', $priceMax);
        });

        if (!empty($selectedCategories)) {
            $categoryIds = $this->categoryIdsIncludingDescendants($selectedCategories);

            if (!empty($categoryIds)) {
                $query->whereHas('categories', function (Builder $query) use ($categoryIds): void {
                    $query->whereIn('taxonomy_terms.id', $categoryIds);
                });
            }
        }

        match ($sort) {
            'price_asc' => $query->orderBy('min_price'),
            'price_desc' => $query->orderByDesc('min_price'),
            'oldest' => $query->oldest('published_at'),
            default => $query->latest('published_at'),
        };

        return [
            'products' => $query
                ->paginate($perPage, ['*'], 'page', $page)
                ->withQueryString(),

            'categories' => $this->categoryTree(),
            'featuredProducts' => $this->featuredProducts(),

            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'availablePriceMin' => $availablePriceMin,
            'availablePriceMax' => $availablePriceMax,
            'selectedCategories' => $selectedCategories,
            'sort' => $sort,
        ];
    }

    private function availablePriceRange(): array
    {
        $min = ProductSaleItem::query()
            ->whereHas('product', fn(Builder $query) => $this->applyPublishedScope($query))
            ->min('base_price');

        $max = ProductSaleItem::query()
            ->whereHas('product', fn(Builder $query) => $this->applyPublishedScope($query))
            ->max('base_price');

        $min = max((int)$min, 0);
        $max = max((int)$max, 0);

        if ($max <= 0) {
            $max = 1000000;
        }

        if ($min > $max) {
            $min = 0;
        }

        return [$min, $max];
    }

    private function applyPublishedScope(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    private function categoryTree()
    {
        return TaxonomyTerm::query()
            ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
            ->whereNull('parent_id')
            ->with([
                'childrenRecursive' => fn($query) => $query->orderBy('title'),
            ])
            ->orderBy('title')
            ->get();
    }

    private function featuredProducts()
    {
        $query = Product::query()
            ->with('picture')
            ->latest('published_at')
            ->limit(5);

        $this->applyPublishedScope($query);

        return $query->get();
    }

    private function categoryIdsIncludingDescendants(array $selectedCategories): array
    {
        return TaxonomyTerm::query()
            ->whereIn('id', $selectedCategories)
            ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
            ->with('childrenRecursive')
            ->get()
            ->flatMap(fn(TaxonomyTerm $term) => $term->selfAndDescendantIds())
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();
    }
}
