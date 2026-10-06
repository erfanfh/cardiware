<?php

namespace App\Application\Vip\Actions;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use Illuminate\Support\Facades\URL;

class GenerateVipSaleItemDownloadUrlAction
{
    public function execute(ProductSaleItem $saleItem, ProductSaleItemFile $file, int $expiresInMinutes = 10): string
    {
        return URL::temporarySignedRoute(
            name: 'downloads.vip-sale-item.file',
            expiration: now()->addMinutes($expiresInMinutes),
            parameters: [
                'saleItem' => $saleItem->getKey(),
                'file' => $file->getKey(),
            ],
        );
    }
}
