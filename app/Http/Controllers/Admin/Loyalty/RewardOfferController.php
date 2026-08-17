<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Application\Loyalty\Actions\Reward\CreateRewardOfferAction;
use App\Application\Loyalty\Actions\Reward\DeleteRewardOfferAction;
use App\Application\Loyalty\Actions\Reward\UpdateRewardOfferAction;
use App\Application\Loyalty\Listings\RewardOfferListing;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Loyalty\IndexRewardOfferRequest;
use App\Http\Requests\Admin\Loyalty\SaveRewardOfferRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class RewardOfferController extends Controller
{
    public function index(IndexRewardOfferRequest $request, RewardOfferListing $listing): View
    {
        Gate::authorize('loyalty-reward.list');
        $validated = $request->validated();

        $rewardOffers = $listing
            ->handle(RewardOffer::query()->withCount('redemptions'), $validated)
            ->paginate($listing->perPage())
            ->withQueryString();

        return view('admin.loyalty.rewards.index', [
            'rewardOffers' => $rewardOffers,
            'types' => $this->typeOptions(),
            'hasActiveFilters' => $listing->hasActiveFilters($validated),
            'bulkActionOptions' => DefaultBulkActions::set()->toOptions(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('loyalty-reward.create');
        return view('admin.loyalty.rewards.create', $this->formData(null));
    }

    /** @throws Throwable */
    public function store(SaveRewardOfferRequest $request, CreateRewardOfferAction $action): RedirectResponse
    {
        Gate::authorize('loyalty-reward.create');
        $rewardOffer = $action->execute($request->validated());

        return redirect()->route('admin.reward-offers.edit', $rewardOffer)
            ->with('success', 'پاداش باشگاه با موفقیت ایجاد شد.');
    }

    public function edit(RewardOffer $rewardOffer): View
    {
        Gate::authorize('loyalty-reward.edit');
        return view('admin.loyalty.rewards.edit', $this->formData($rewardOffer));
    }

    /** @throws Throwable */
    public function update(SaveRewardOfferRequest $request, RewardOffer $rewardOffer, UpdateRewardOfferAction $action): RedirectResponse
    {
        Gate::authorize('loyalty-reward.edit');
        $rewardOffer = $action->execute($rewardOffer, $request->validated());

        return redirect()->route('admin.reward-offers.edit', $rewardOffer)
            ->with('success', 'پاداش باشگاه با موفقیت بروزرسانی شد.');
    }

    /** @throws Throwable */
    public function destroy(RewardOffer $rewardOffer, DeleteRewardOfferAction $action): RedirectResponse
    {
        Gate::authorize('loyalty-reward.delete');
        $action->execute($rewardOffer);

        return redirect()->route('admin.reward-offers.index')
            ->with('success', 'پاداش باشگاه با موفقیت حذف شد.');
    }

    private function formData(?RewardOffer $rewardOffer): array
    {
        return [
            'rewardOffer' => $rewardOffer,
            'types' => $this->typeOptions(),
            'couponTypes' => collect(CouponTypeEnum::cases())->mapWithKeys(fn($type) => [$type->value => $type->label()])->all(),
            'defaultConfig' => [
                'coupon_type' => CouponTypeEnum::FIXED_AMOUNT->value,
                'coupon_value' => 100000,
                'max_discount_amount' => null,
                'minimum_order_amount' => 0,
                'expires_after_days' => 7,
                'applies_to_addons' => false,
            ],
        ];
    }

    private function typeOptions(): array
    {
        return collect(RewardTypeEnum::cases())->mapWithKeys(fn($type) => [$type->value => $type->label()])->all();
    }
}
