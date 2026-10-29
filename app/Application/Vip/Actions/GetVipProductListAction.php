<?php

namespace App\Application\Vip\Actions;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetVipProductListAction
{
    public function execute(int $page = 1, int $perPage = 12, string $sort = 'latest'): LengthAwarePaginator
    {
        $sort = $sort === 'oldest' ? 'oldest' : 'latest';

        return app(PublicReadCache::class)->paginate(
            'vip-products', [$page, $perPage, $sort],
            [Product::class, ProductSaleItem::class, ProductSaleItemDiscount::class, ProductSaleItemFile::class, Store::class, MediaFile::class, TaxonomyTerm::class],
            fn () => $this->query($page, $perPage, $sort),
            [Product::class => ['published_at']],
        );
    }

    private function query(int $page, int $perPage, string $sort): LengthAwarePaginator
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
