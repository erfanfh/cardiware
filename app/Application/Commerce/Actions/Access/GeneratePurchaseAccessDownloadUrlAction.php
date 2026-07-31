<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Support\Facades\URL;

class GeneratePurchaseAccessDownloadUrlAction
{
    /**
     * Generate a temporary signed download URL.
     */
    public function execute(
        PurchaseAccess      $purchaseAccess,
        ProductSaleItemFile $file,
        int                 $expiresInMinutes = 10,
        bool                $alreadyRecorded = false,
    ): string
    {
        return URL::temporarySignedRoute(
            name: 'downloads.purchase-access.file',
            expiration: now()->addMinutes($expiresInMinutes),
            parameters: [
                'purchaseAccess' => $purchaseAccess->id,
                'file' => $file->id,
                'prepared' => $alreadyRecorded ? 1 : 0,
            ],
        );
    }
}
