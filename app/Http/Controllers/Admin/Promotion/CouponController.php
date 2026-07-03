<?php

namespace App\Http\Controllers\Admin\Promotion;

use App\Application\Promotion\Actions\Coupon\CreateCouponAction;
use App\Application\Promotion\Actions\Coupon\GetCouponFormDataAction;
use App\Application\Promotion\Actions\Coupon\UpdateCouponAction;
use App\Application\Promotion\Listings\CouponListing;
use App\Domain\Promotion\Enums\CouponReservationStatusEnum;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Promotion\IndexCouponRequest;
use App\Http\Requests\Admin\Promotion\UpdateCouponRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class CouponController extends Controller
{
    public function index(IndexCouponRequest $request, CouponListing $couponListing): View
    {
        $coupons = $couponListing
            ->handle(
                Coupon::query()
                    ->withCount([
                        'users',
                        'targets',
                        'reservations as active_reservations_count' => function ($query) {
                            $query
                                ->where('status', CouponReservationStatusEnum::ACTIVE)
                                ->where('expires_at', '>', now());
                        },
                    ]),
                $request->validated()
            )
            ->paginate($couponListing->perPage())
            ->withQueryString();

        $statuses = collect(CouponStatusEnum::cases())
            ->mapWithKeys(fn(CouponStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $types = collect(CouponTypeEnum::cases())
            ->mapWithKeys(fn(CouponTypeEnum $type) => [
                $type->value => $type->label(),
            ])
            ->all();

        $scopes = collect(CouponScopeEnum::cases())
            ->mapWithKeys(fn(CouponScopeEnum $scope) => [
                $scope->value => $scope->label(),
            ])
            ->all();

        $hasActiveFilters = $couponListing->hasActiveFilters($request->validated());

        $bulkActionOptions = DefaultBulkActions::set()->toOptions();

        return view('admin.promotion.coupons.index', compact(
            'coupons',
            'statuses',
            'types',
            'scopes',
            'hasActiveFilters',
            'bulkActionOptions',
        ));
    }

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
