<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Support\Collection;

class GetUserPurchaseAccessesAction
{
    /**
     * Get user's purchase accesses for downloads page.
     *
     * @return Collection<int, PurchaseAccess>
     */
    public function execute(int $userId): Collection
    {
        return PurchaseAccess::query()
            ->where('user_id', $userId)
            ->with([
                'product.picture',
                'product.installationSteps',
                'productSaleItem.files',
            ])
            ->latest('id')
            ->get();
    }
}
