<?php

namespace App\Application\Launcher\Actions;

use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GetLauncherCatalogAction
{
    public function execute(User $user, ?string $search, int $perPage): LengthAwarePaginator
    {
        return ProductSaleItem::query()
            ->where('launcher_enabled', true)
            ->where('status', ProductSaleItemStatusEnum::AVAILABLE->value)
            ->whereHas(
                'product',
                fn(Builder $query): Builder => $query->where('status', ProductStatusEnum::ACTIVE->value)
            )
            ->with([
                'product.picture',
                'discount',
                'purchaseAccesses' => function (HasMany $relation) use ($user): void {
                    $relation->withoutGlobalScopes()
                        ->where('user_id', $user->getKey())
                        ->latest('id');
                },
            ])
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('launcher_title', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('product', fn(Builder $product): Builder => $product->where('title', 'like', "%{$search}%"));
                });
            })
            ->orderBy('launcher_sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(User $user, int $saleItemId): ProductSaleItem
    {
        return ProductSaleItem::query()
            ->whereKey($saleItemId)
            ->where('launcher_enabled', true)
            ->whereHas(
                'product',
                fn(Builder $query): Builder => $query->where('status', ProductStatusEnum::ACTIVE->value)
            )
            ->with([
                'product.picture',
                'discount',
                'manifest',
                'purchaseAccesses' => function (HasMany $relation) use ($user): void {
                    $relation->withoutGlobalScopes()
                        ->where('user_id', $user->getKey())
                        ->latest('id');
                },
            ])
            ->firstOrFail();
    }
}
