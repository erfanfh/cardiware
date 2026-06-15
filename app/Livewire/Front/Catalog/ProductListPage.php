<?php

namespace App\Livewire\Front\Catalog;

use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ProductListPage extends Component
{
    use WithPagination;

    public int $priceMin = 0;

    public int $priceMax = 0;

    public int $availablePriceMin = 0;

    public int $availablePriceMax = 0;

    /**
     * @var array<int|string>
     */
    public array $selectedCategories = [];

    public string $sort = 'latest';

    protected $queryString = [
        'priceMin' => ['except' => 0],
        'priceMax' => ['except' => 0],
        'selectedCategories' => ['except' => [], 'as' => 'categories'],
        'sort' => ['except' => 'latest'],
    ];

    public function mount(): void
    {
        $this->loadAvailablePriceRange();

        if ($this->priceMin <= 0) {
            $this->priceMin = $this->availablePriceMin;
        }

        if ($this->priceMax <= 0) {
            $this->priceMax = $this->availablePriceMax;
        }

        $this->normalizePriceRange();
        $this->normalizeSelectedCategories();
    }

    public function updatedPriceMin(): void
    {
        $this->normalizePriceRange();
        $this->resetPage();
    }

    public function updatedPriceMax(): void
    {
        $this->normalizePriceRange();
        $this->resetPage();
    }

    public function updatedSelectedCategories(): void
    {
        $this->normalizeSelectedCategories();
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->priceMin = $this->availablePriceMin;
        $this->priceMax = $this->availablePriceMax;
        $this->selectedCategories = [];
        $this->sort = 'latest';

        $this->resetPage();
    }

    public function render(): View
    {
        $productsPerPage = (int)(get_setting('products', 'products_per_page') ?: 12);

        $productsQuery = Product::query()
            ->with([
                'picture',
                'categories',
                'saleItems:id,product_id,base_price,status,sort_order',
                'store:id,title,slug',
            ])
            ->withMin('saleItems as min_price', 'base_price')
            ->withMax('saleItems as max_price', 'base_price');

        $this->applyPublishedScope($productsQuery);
        $this->applyPriceFilter($productsQuery);
        $this->applyCategoryFilter($productsQuery);
        $this->applySort($productsQuery);

        return view('livewire.front.catalog.product-list-page', [
            'products' => $productsQuery->paginate($productsPerPage),
            'categories' => $this->getCategoryTree(),
            'featuredProducts' => $this->getFeaturedProducts(),
        ]);
    }

    private function loadAvailablePriceRange(): void
    {
        $min = ProductSaleItem::query()
            ->whereHas('product', fn(Builder $query) => $this->applyPublishedScope($query))
            ->min('base_price');

        $max = ProductSaleItem::query()
            ->whereHas('product', fn(Builder $query) => $this->applyPublishedScope($query))
            ->max('base_price');

        $this->availablePriceMin = max((int)$min, 0);
        $this->availablePriceMax = max((int)$max, 0);

        if ($this->availablePriceMax <= 0) {
            $this->availablePriceMax = 1000000;
        }

        if ($this->availablePriceMin > $this->availablePriceMax) {
            $this->availablePriceMin = 0;
        }
    }

    private function applyPublishedScope(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    private function applyPriceFilter(Builder $query): void
    {
        $query->whereHas('saleItems', function (Builder $query): void {
            $query
                ->where('base_price', '>=', $this->priceMin)
                ->where('base_price', '<=', $this->priceMax);
        });
    }

    private function applyCategoryFilter(Builder $query): void
    {
        if (empty($this->selectedCategories)) {
            return;
        }

        $selectedCategoryIds = $this->getSelectedCategoryIdsIncludingDescendants();

        if (empty($selectedCategoryIds)) {
            return;
        }

        $query->whereHas('categories', function (Builder $query) use ($selectedCategoryIds): void {
            $query->whereIn('taxonomy_terms.id', $selectedCategoryIds);
        });
    }

    private function applySort(Builder $query): void
    {
        match ($this->sort) {
            'price_asc' => $query->orderBy('min_price'),
            'price_desc' => $query->orderByDesc('min_price'),
            'oldest' => $query->oldest('published_at'),
            default => $query->latest('published_at'),
        };
    }

    private function getCategoryTree(): Collection
    {
        return TaxonomyTerm::query()
            ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
            ->whereNull('parent_id')
            ->with([
                'childrenRecursive' => function ($query): void {
                    $query->orderBy('title');
                },
            ])
            ->orderBy('title')
            ->get();
    }

    private function getFeaturedProducts(): Collection
    {
        $query = Product::query()
            ->with([
                'picture',
            ])
            ->latest('published_at')
            ->limit(5);

        $this->applyPublishedScope($query);

        return $query->get();
    }

    /**
     * @return array<int>
     */
    private function getSelectedCategoryIdsIncludingDescendants(): array
    {
        return TaxonomyTerm::query()
            ->whereIn('id', $this->selectedCategories)
            ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
            ->with('childrenRecursive')
            ->get()
            ->flatMap(fn(TaxonomyTerm $term) => $term->selfAndDescendantIds())
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizePriceRange(): void
    {
        $this->priceMin = (int)$this->priceMin;
        $this->priceMax = (int)$this->priceMax;

        if ($this->priceMin < $this->availablePriceMin) {
            $this->priceMin = $this->availablePriceMin;
        }

        if ($this->priceMax > $this->availablePriceMax) {
            $this->priceMax = $this->availablePriceMax;
        }

        if ($this->priceMin > $this->priceMax) {
            [$this->priceMin, $this->priceMax] = [$this->priceMax, $this->priceMin];
        }
    }

    private function normalizeSelectedCategories(): void
    {
        $this->selectedCategories = collect($this->selectedCategories)
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();
    }
}
