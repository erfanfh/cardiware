<?php

namespace App\Http\Controllers\Admin\Promotion;

use App\Application\Promotion\Actions\Coupon\HandleCouponBulkAction;
use App\Http\Requests\Admin\Promotion\BulkCouponActionRequest;
use Throwable;

class CouponBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkCouponActionRequest $request,
        HandleCouponBulkAction  $actionHandler
    )
    {
        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->couponIds(),
        );

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', $actionHandler->successMessage($result));
    }
}
