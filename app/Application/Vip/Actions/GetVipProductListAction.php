<?php

namespace App\Application\Vip\Actions;

use App\Domain\Catalog\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetVipProductListAction
{
    public function execute(int $page = 1, int $perPage = 12, string $sort = 'latest'): LengthAwarePaginator
    {
        $query = Product::query()
            ->whereIn('products.id', app(GetVipDownloadableSaleItemsAction::class)
                ->query()
                ->select('product_id'))
            ->with([
                'picture.primaryVariant',
                'picture.variants',
                'categories',
                'store:id,title,slug',
                'saleItems.discount',
            ]);

        if ($sort === 'oldest') {
            $query->oldest('published_at')->orderBy('id');
        } else {
            $query->latest('published_at')->orderByDesc('id');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
