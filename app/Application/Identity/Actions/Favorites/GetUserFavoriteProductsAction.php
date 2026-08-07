<?php

namespace App\Application\Identity\Actions\Favorites;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class GetUserFavoriteProductsAction
{
    public function __construct(
        private readonly GetUserFavoriteProductIdsAction $getUserFavoriteProductIdsAction,
    )
    {
    }

    public function execute(
        User $user,
        int $perPage = 12,
        string $pageName = 'page',
    ): LengthAwarePaginatorContract
    {
        $favoriteProductIds = $this->getUserFavoriteProductIdsAction->execute($user);
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);

        $pageProductIds = $favoriteProductIds
            ->forPage($currentPage, $perPage)
            ->values();

        $products = $this->getProducts($pageProductIds);

        return new LengthAwarePaginator(
            items: $products,
            total: $favoriteProductIds->count(),
            perPage: $perPage,
            currentPage: $currentPage,
            options: [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ],
        );
    }

    /**
     * @param Collection<int, int> $productIds
     * @return Collection<int, Product>
     */
    private function getProducts(Collection $productIds): Collection
    {
        if ($productIds->isEmpty()) {
            return collect();
        }

        $order = $productIds
            ->values()
            ->flip();

        return Product::query()
            ->whereKey($productIds->all())
            ->with([
                'picture.primaryVariant',
                'store.picture',
                'categories',
                'saleItems.discount',
            ])
            ->get()
            ->sortBy(static fn(Product $product): int => $order->get((int)$product->getKey(), PHP_INT_MAX))
            ->values();
    }
}
