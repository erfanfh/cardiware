<?php

namespace App\Application\Catalog\Actions\ProductInformation;


use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncProductInformationAction
{
    public function __construct(
        private readonly SyncProductGlobalAttributesAction $syncGlobalAttributes,
        private readonly SyncProductCustomAttributesAction $syncCustomAttributes,
        private readonly SyncProductSaleItemsAction        $syncSaleItems,
    )
    {
    }

    /**
     * Sync all product information box data.
     *
     * @param Product $product
     * @param array $payload
     * @return void
     * @throws Throwable
     */
    public function execute(Product $product, array $payload): void
    {
        DB::transaction(function () use ($product, $payload) {
            $this->syncGlobalAttributes->execute(
                product: $product,
                items: $payload['attributes'] ?? []
            );

            $customAttributeValueKeyMap = $this->syncCustomAttributes->execute(
                product: $product,
                items: $payload['custom_attributes'] ?? []
            );

            $this->syncSaleItems->execute(
                product: $product,
                payload: $payload,
                customAttributeValueKeyMap: $customAttributeValueKeyMap
            );
        });
    }
}
