<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GetCatalogProductsAction
{
    public function __construct(
        private readonly HydrateCatalogProductOwnershipAction $hydrateOwnershipAction,
    )
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(
        ?User $user,
        array $filters,
        int   $perPage = 20,
    ): LengthAwarePaginator
    {
        $relations = [
            'picture',
            'store.picture',
            'categories',
            'saleItems.discount',
        ];

        if ($user !== null) {
            $relations['purchaseAccesses'] = function (
                HasMany $relation,
            ) use ($user): void {
                $relation
                    ->withoutGlobalScopes()
                    ->where('user_id', $user->getKey())
                    ->select([
                        'id',
                        'user_id',
                        'product_id',
                        'product_sale_item_id',
                        'status',
                        'starts_at',
                        'expires_at',
                    ]);
            };
        }

        $query = Product::query()
            ->with($relations);

        $query->when(
            $filters['search'] ?? null,
            function (Builder $query, string $search): void {
                $query->where(
                    function (Builder $query) use ($search): void {
                        $query
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhereHas(
                                'saleItems',
                                function (Builder $query) use ($search): void {
                                    $query
                                        ->where(
                                            'title',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'sku',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    }
                );
            }
        );

        $query->when(
            $filters['category'] ?? null,
            function (Builder $query, string $category): void {
                $query->whereHas(
                    'categories',
                    function (Builder $query) use ($category): void {
                        $query->where(
                            'taxonomy_terms.path',
                            $category
                        );
                    }
                );
            }
        );

        $query->when(
            $filters['tag'] ?? null,
            function (Builder $query, string $tag): void {
                $query->whereHas(
                    'tags',
                    function (Builder $query) use ($tag): void {
                        $query->where(
                            'taxonomy_terms.path',
                            $tag
                        );
                    }
                );
            }
        );

        $query->when(
            $filters['type'] ?? null,
            fn(Builder $query, string $type): Builder => $query->where('type', $type)
        );

        $query->when(
            $filters['structure'] ?? null,
            fn(Builder $query, string $structure): Builder => $query->where('structure', $structure)
        );

        match ($filters['sort'] ?? 'latest') {
            'oldest' => $query
                ->orderBy('published_at')
                ->orderBy('id'),

            'title_asc' => $query
                ->orderBy('title')
                ->orderBy('id'),

            'title_desc' => $query
                ->orderByDesc('title')
                ->orderByDesc('id'),

            default => $query
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
        };

        $products = $query
            ->paginate($perPage)
            ->withQueryString();

        $this->hydrateOwnershipAction->execute(
            products: $products->getCollection(),
            user: $user,
        );

        return $products;
    }
}
