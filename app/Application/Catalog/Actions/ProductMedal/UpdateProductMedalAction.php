<?php

namespace App\Application\Catalog\Actions\ProductMedal;

use App\Application\Shared\Cache\Invalidators\ProductCacheInvalidator;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductMedal;

class UpdateProductMedalAction
{
    public function __construct(
        protected ProductCacheInvalidator $productCacheInvalidator,
    )
    {
    }

    public function execute(
        ProductMedal $medal,
        array        $data
    ): ProductMedal
    {
        /*
         * شناسه محصولاتی که این مدال روی آن‌ها استفاده شده،
         * قبل از به‌روزرسانی نگهداری می‌شود تا Cache آن‌ها پاک شود.
         */
        $productIds = $medal->assignments()
            ->pluck('product_id')
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        $medal->update([
            'title' => trim((string)$data['title']),
            'picture_id' => (int)$data['picture_id'],
            'is_active' => (bool)$data['is_active'],
        ]);

        if (!empty($productIds)) {
            Product::query()
                ->whereKey($productIds)
                ->eachById(function (Product $product): void {
                    $this->productCacheInvalidator->afterUpdated($product);
                });
        }

        return $medal->refresh();
    }
}
