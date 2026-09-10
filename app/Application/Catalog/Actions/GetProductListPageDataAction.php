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
    public function execute(
        Request $request,
        int $page = 1,
        int $perPage = 12,
        ?TaxonomyTerm $fixedCategory = null,
    ): array {
        $fixedCategoryIds = $fixedCategory?->selfAndDescendantIds() ?? [];

        [$availablePriceMin, $availablePriceMax] = $this->availablePriceRange(
            categoryIds: $fixedCategoryIds,
            categoryId: $fixedCategory?->getKey(),
        );

        $filters = $this->normalizeFilters(
            request: $request,
            availablePriceMin: $availablePriceMin,
            availablePriceMax: $availablePriceMax
        );

        $products = $this->products(
            filters: $filters,
            page: $page,
            perPage: $perPage,
            fixedCategoryIds: $fixedCategoryIds,
            fixedCategoryId: $fixedCategory?->getKey(),
        )->withQueryString();

        return [
            'products' => $products,

            'categories' => $this->categoryTree($fixedCategory),
            'featuredProducts' => $this->featuredProducts(
                categoryIds: $fixedCategoryIds,
                categoryId: $fixedCategory?->getKey(),
            ),

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
        int $availablePriceMin,
        int $availablePriceMax
    ): array {
        $priceMin = (int) $request->query('priceMin', $availablePriceMin);
        $priceMax = (int) $request->query('priceMax', $availablePriceMax);

        $priceMin = max($priceMin, $availablePriceMin);
        $priceMax = min($priceMax, $availablePriceMax);

        if ($priceMin > $priceMax) {
            [$priceMin, $priceMax] = [$priceMax, $priceMin];
        }

        $selectedCategories = collect((array) $request->query('categories', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $sort = (string) $request->query('sort', 'latest');

        if (! in_array($sort, ['latest', 'oldest', 'price_asc', 'price_desc'], true)) {
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
    private function products(
        array $filters,
        int $page,
        int $perPage,
        array $fixedCategoryIds = [],
        int|string|null $fixedCategoryId = null,
    ): LengthAwarePaginator {
        $payload = [
            'page' => $page,
            'perPage' => $perPage,
            'priceMin' => $filters['priceMin'],
            'priceMax' => $filters['priceMax'],
            'selectedCategories' => $filters['selectedCategories'],
            'fixedCategoryId' => $fixedCategoryId,
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
            fn () => $this->queryProducts(
                filters: $filters,
                page: $page,
                perPage: $perPage,
                fixedCategoryIds: $fixedCategoryIds,
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
    private function queryProducts(
        array $filters,
        int $page,
        int $perPage,
        array $fixedCategoryIds = [],
    ): LengthAwarePaginator {
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
        $this->applyCategoryConstraint($query, $fixedCategoryIds);

        $query->whereHas('saleItems', function (Builder $query) use ($filters): void {
            $query
                ->where('base_price', '>=', $filters['priceMin'])
                ->where('base_price', '<=', $filters['priceMax']);
        });

        if (! empty($filters['selectedCategories'])) {
            $categoryIds = $this->categoryIdsIncludingDescendants($filters['selectedCategories']);

            if (! empty($categoryIds)) {
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
    private function availablePriceRange(array $categoryIds = [], int|string|null $categoryId = null): array
    {
        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::PRODUCT_FILTERS,
        ])->remember(
            CacheKey::productAvailablePriceRange($categoryId),
            CacheTtl::medium(),
            function () use ($categoryIds): array {
                $row = ProductSaleItem::query()
                    ->whereHas('product', function (Builder $query) use ($categoryIds): void {
                        $this->applyPublishedScope($query);
                        $this->applyCategoryConstraint($query, $categoryIds);
                    })
                    ->selectRaw('MIN(base_price) as min_price, MAX(base_price) as max_price')
                    ->first();

                $min = max((int) ($row?->min_price ?? 0), 0);
                $max = max((int) ($row?->max_price ?? 0), 0);

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

    private function categoryTree(?TaxonomyTerm $fixedCategory = null): Collection
    {
        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::CATEGORIES,
        ])->remember(
            CacheKey::productCategoryTree($fixedCategory?->getKey()),
            CacheTtl::long(),
            fn () => TaxonomyTerm::query()
                ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
                ->when(
                    $fixedCategory,
                    fn (Builder $query) => $query->where('parent_id', $fixedCategory->getKey()),
                    fn (Builder $query) => $query->whereNull('parent_id'),
                )
                ->with([
                    'childrenRecursive' => fn ($query) => $query->orderBy('title'),
                ])
                ->orderBy('title')
                ->get()
        );
    }

    private function featuredProducts(
        array $categoryIds = [],
        int|string|null $categoryId = null,
    ): Collection {
        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::PRODUCT_FEATURED,
        ])->remember(
            CacheKey::productFeatured($categoryId),
            CacheTtl::medium(),
            function () use ($categoryIds) {
                $query = Product::query()
                    ->with([
                        'picture.primaryVariant',
                        'picture.variants',
                    ])
                    ->latest('published_at')
                    ->limit(5);

                $this->applyPublishedScope($query);
                $this->applyCategoryConstraint($query, $categoryIds);

                return $query->get();
            }
        );
    }

    /**
     * @param  array<int>  $selectedCategories
     * @return array<int>
     */
    private function categoryIdsIncludingDescendants(array $selectedCategories): array
    {
        $selectedCategories = collect($selectedCategories)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
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
            fn () => TaxonomyTerm::query()
                ->whereIn('id', $selectedCategories)
                ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
                ->with('childrenRecursive')
                ->get()
                ->flatMap(fn (TaxonomyTerm $term) => $term->selfAndDescendantIds())
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all()
        );
    }

    /**
     * @param  array<int>  $categoryIds
     */
    private function applyCategoryConstraint(Builder $query, array $categoryIds): Builder
    {
        if ($categoryIds === []) {
            return $query;
        }

        return $query->whereHas('categories', function (Builder $query) use ($categoryIds): void {
            $query->whereIn('taxonomy_terms.id', $categoryIds);
        });
    }
}
