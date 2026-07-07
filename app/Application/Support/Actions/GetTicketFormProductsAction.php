<?php

namespace App\Application\Support\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GetTicketFormProductsAction
{
    public function execute(User $user): Collection
    {
        return Product::query()
            ->whereHas('saleItems', function (Builder $query) use ($user) {
                $query->whereIn('id', $user->purchaseAccesses()->select('product_sale_item_id'));
            })
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'slug',
                'status',
            ]);
    }
}
