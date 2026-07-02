<?php

namespace App\Http\Controllers\Admin\Promotion;

use App\Application\Promotion\Actions\Coupon\CreateCouponAction;
use App\Application\Promotion\Actions\Coupon\GetCouponFormDataAction;
use App\Application\Promotion\Actions\Coupon\UpdateCouponAction;
use App\Domain\Promotion\Models\Coupon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Promotion\UpdateCouponRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class CouponController extends Controller
{
    public function create(): View
    {
        return view('admin.promotion.coupons.create');
    }

    /**
     * @throws Throwable
     */
    public function store(UpdateCouponRequest $request): RedirectResponse
    {
        $coupon = app(CreateCouponAction::class)->execute(
            data: $request->validated(),
            creator: $request->user()
        );

        return redirect()
            ->route('admin.coupons.edit', $coupon)
            ->with('success', 'کدتخفیف با موفقیت ایجاد شد.');
    }

    public function edit(Coupon $coupon): View
    {
        $coupon->load([
            'users',
            'targets',
        ]);

        return view('admin.promotion.coupons.edit',
            app(GetCouponFormDataAction::class)->execute($coupon)
        );
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon = app(UpdateCouponAction::class)->execute(
            coupon: $coupon,
            data: $request->validated()
        );

        return redirect()
            ->route('admin.coupons.edit', $coupon)
            ->with('success', 'کدتخفیف با موفقیت بروزرسانی شد.');
    }
}
