<?php

namespace App\Application\Catalog\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetProductListPageDataAction
{
    /**
     * @return array{
     *     products: LengthAwarePaginator,
     *     categories: Collection,
     *     featuredProducts: Collection,
     *     priceMin: int,
     *     priceMax: int,
     *     availablePriceMin: int,
     *     availablePriceMax: int,
     *     selectedCategories: array<int>,
     *     sort: string
     * }
     */
    public function execute(Request $request, int $page = 1, int $perPage = 12): array
    {
        [$availablePriceMin, $availablePriceMax] = $this->availablePriceRange();

        $filters = $this->normalizeFilters(
            request: $request,
            availablePriceMin: $availablePriceMin,
            availablePriceMax: $availablePriceMax
        );

        $products = $this->products(
            filters: $filters,
            page: $page,
            perPage: $perPage
        )->withQueryString();

        return [
            'products' => $products,

            'categories' => $this->categoryTree(),
            'featuredProducts' => $this->featuredProducts(),

            'priceMin' => $filters['priceMin'],
            'priceMax' => $filters['priceMax'],
            'availablePriceMin' => $availablePriceMin,
            'availablePriceMax' => $availablePriceMax,
            'selectedCategories' => $filters['selectedCategories'],
            'sort' => $filters['sort'],
        ];
    }

    /**
     * @return array{
     *     priceMin: int,
     *     priceMax: int,
     *     selectedCategories: array<int>,
     *     sort: string
     * }
     */
    private function normalizeFilters(
        Request $request,
        int     $availablePriceMin,
        int     $availablePriceMax
    ): array
    {
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
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $sort = (string)$request->query('sort', 'latest');

        if (!in_array($sort, ['latest', 'oldest', 'price_asc', 'price_desc'], true)) {
            $sort = 'latest';
        }

        return [
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'selectedCategories' => $selectedCategories,
            'sort' => $sort,
        ];
    }

    /**
     * @param array{
     *     priceMin: int,
     *     priceMax: int,
     *     selectedCategories: array<int>,
     *     sort: string
     * } $filters
     */
    private function products(array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        $payload = [
            'page' => $page,
            'perPage' => $perPage,
            'priceMin' => $filters['priceMin'],
            'priceMax' => $filters['priceMax'],
            'selectedCategories' => $filters['selectedCategories'],
            'sort' => $filters['sort'],
        ];

        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::PRODUCT_FILTERS,
            CacheTag::CATEGORIES,
        ])->remember(
            CacheKey::productListPage($payload),
            CacheTtl::medium(),
            fn() => $this->queryProducts(
                filters: $filters,
                page: $page,
                perPage: $perPage
            )
        );
    }

    /**
     * @param array{
     *     priceMin: int,
     *     priceMax: int,
     *     selectedCategories: array<int>,
     *     sort: string
     * } $filters
     */
    private function queryProducts(array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        $query = Product::query()
            ->with([
                'picture.primaryVariant',
                'picture.variants',
                'categories',
                'saleItems:id,product_id,base_price,status,sort_order',
                'store:id,title,slug',
            ])
            ->withMin('saleItems as min_price', 'base_price')
            ->withMax('saleItems as max_price', 'base_price');

        $this->applyPublishedScope($query);

        $query->whereHas('saleItems', function (Builder $query) use ($filters): void {
            $query
                ->where('base_price', '>=', $filters['priceMin'])
                ->where('base_price', '<=', $filters['priceMax']);
        });

        if (!empty($filters['selectedCategories'])) {
            $categoryIds = $this->categoryIdsIncludingDescendants($filters['selectedCategories']);

            if (!empty($categoryIds)) {
                $query->whereHas('categories', function (Builder $query) use ($categoryIds): void {
                    $query->whereIn('taxonomy_terms.id', $categoryIds);
                });
            }
        }

        match ($filters['sort']) {
            'price_asc' => $query->orderBy('min_price'),
            'price_desc' => $query->orderByDesc('min_price'),
            'oldest' => $query->oldest('published_at'),
            default => $query->latest('published_at'),
        };

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function availablePriceRange(): array
    {
        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::PRODUCT_FILTERS,
        ])->remember(
            CacheKey::productAvailablePriceRange(),
            CacheTtl::medium(),
            function (): array {
                $row = ProductSaleItem::query()
                    ->whereHas('product', fn(Builder $query) => $this->applyPublishedScope($query))
                    ->selectRaw('MIN(base_price) as min_price, MAX(base_price) as max_price')
                    ->first();

                $min = max((int)($row?->min_price ?? 0), 0);
                $max = max((int)($row?->max_price ?? 0), 0);

                if ($max <= 0) {
                    $max = 1000000;
                }

                if ($min > $max) {
                    $min = 0;
                }

                return [$min, $max];
            }
        );
    }

    private function applyPublishedScope(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    private function categoryTree(): Collection
    {
        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::CATEGORIES,
        ])->remember(
            CacheKey::productCategoryTree(),
            CacheTtl::long(),
            fn() => TaxonomyTerm::query()
                ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
                ->whereNull('parent_id')
                ->with([
                    'childrenRecursive' => fn($query) => $query->orderBy('title'),
                ])
                ->orderBy('title')
                ->get()
        );
    }

    private function featuredProducts(): Collection
    {
        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::PRODUCT_FEATURED,
        ])->remember(
            CacheKey::productFeatured(),
            CacheTtl::medium(),
            function () {
                $query = Product::query()
                    ->with([
                        'picture.primaryVariant',
                        'picture.variants',
                    ])
                    ->latest('published_at')
                    ->limit(5);

                $this->applyPublishedScope($query);

                return $query->get();
            }
        );
    }

    /**
     * @param array<int> $selectedCategories
     * @return array<int>
     */
    private function categoryIdsIncludingDescendants(array $selectedCategories): array
    {
        $selectedCategories = collect($selectedCategories)
            ->map(fn($id) => (int)$id)
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($selectedCategories)) {
            return [];
        }

        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::CATEGORIES,
        ])->remember(
            CacheKey::productCategoryDescendants($selectedCategories),
            CacheTtl::long(),
            fn() => TaxonomyTerm::query()
                ->whereIn('id', $selectedCategories)
                ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
                ->with('childrenRecursive')
                ->get()
                ->flatMap(fn(TaxonomyTerm $term) => $term->selfAndDescendantIds())
                ->map(fn($id) => (int)$id)
                ->unique()
                ->values()
                ->all()
        );
    }
}
