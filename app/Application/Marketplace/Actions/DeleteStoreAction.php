<?php

namespace App\Application\Marketplace\Actions;

use App\Application\Marketplace\DTOs\DeleteStoreResultDTO;
use App\Application\Shared\Cache\Invalidators\StoreCacheInvalidator;
use App\Domain\Marketplace\Models\Store;

class DeleteStoreAction
{
    public function __construct(
        protected StoreCacheInvalidator $storeCacheInvalidator,
    )
    {
    }

    public function execute(Store $store): DeleteStoreResultDTO
    {
        $productsCount = $store->products()->count();
        if ($productsCount > 0) {
            return new DeleteStoreResultDTO(
                status: false,
                message: "امکان حذف این فروشنده وجود ندارد. $productsCount محصول متعلق به این فروشنده می باشد.",
            );
        }

        $this->storeCacheInvalidator->afterDeleted($store);

        $store->delete();

        return new DeleteStoreResultDTO(
            status: true,
        );
    }
}
