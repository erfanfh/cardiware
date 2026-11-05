<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUserPurchaseAccessesAction
{
    /**
     * Get user's purchase accesses for downloads page.
     *
     */
    public function execute(int $userId, int $perPage = 5): LengthAwarePaginator
    {
        return PurchaseAccess::query()
            ->where('user_id', $userId)
            // دسترسی امانی VIP خرید نیست و در «دانلودهای من» نمایش داده نمی‌شود.
            ->owned()
            ->with([
                'product.picture',
                'product.installationSteps',
                'productSaleItem.files',
            ])
            ->latest('id')
            ->paginate($perPage);
    }
}
