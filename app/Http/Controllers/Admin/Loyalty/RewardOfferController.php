<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Application\Loyalty\Actions\RewardOffer\CreateRewardOfferAction;
use App\Application\Loyalty\Actions\RewardOffer\DeleteRewardOfferAction;
use App\Application\Loyalty\Actions\RewardOffer\UpdateRewardOfferAction;
use App\Application\Loyalty\Listings\RewardOfferListing;
use App\Application\Loyalty\RewardFulfillment\Support\RewardOfferConfigCatalog;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Loyalty\IndexRewardOfferRequest;
use App\Http\Requests\Admin\Loyalty\SaveRewardOfferRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class RewardOfferController extends Controller
{
    public function index(IndexRewardOfferRequest $request, RewardOfferListing $listing): View
    {
        Gate::authorize('loyalty-reward.list');

        $validated = $request->validated();
        $rewardOffers = $listing
            ->handle(RewardOffer::query()->with('target'), $validated)
            ->paginate($listing->perPage())
            ->withQueryString();

        $types = collect(RewardTypeEnum::cases())
            ->mapWithKeys(fn(RewardTypeEnum $type) => [$type->value => $type->label()])
            ->all();

        $hasActiveFilters = $listing->hasActiveFilters($validated);
        $bulkActionOptions = [
            'activate' => 'فعال سازی',
            'deactivate' => 'غیرفعال سازی',
            'delete' => 'حذف',
        ];

        return view('admin.loyalty.rewards.index', compact(
            'rewardOffers',
            'types',
            'hasActiveFilters',
            'bulkActionOptions',
        ));
    }

    public function create(RewardOfferConfigCatalog $catalog): View
    {
        Gate::authorize('loyalty-reward.create');

        return view('admin.loyalty.rewards.create', $this->formData($catalog));
    }

    /** @throws Throwable */
    public function store(
        SaveRewardOfferRequest $request,
        CreateRewardOfferAction $action,
    ): RedirectResponse {
        Gate::authorize('loyalty-reward.create');

        $rewardOffer = $action->execute($request->validated());

        return redirect()
            ->route('admin.reward-offers.edit', $rewardOffer)
            ->with('success', 'پاداش با موفقیت ایجاد شد.');
    }

    public function edit(
        RewardOffer $rewardOffer,
        RewardOfferConfigCatalog $catalog,
    ): View {
        Gate::authorize('loyalty-reward.edit');

        $rewardOffer->loadMissing('target')->loadCount('redemptions');

        return view('admin.loyalty.rewards.edit', array_merge(
            ['rewardOffer' => $rewardOffer],
            $this->formData($catalog, $rewardOffer),
        ));
    }

    /** @throws Throwable */
    public function update(
        SaveRewardOfferRequest $request,
        RewardOffer $rewardOffer,
        UpdateRewardOfferAction $action,
    ): RedirectResponse {
        Gate::authorize('loyalty-reward.edit');

        $action->execute($rewardOffer, $request->validated());

        return back()->with('success', 'پاداش با موفقیت بروزرسانی شد.');
    }

    /** @throws Throwable */
    public function destroy(
        RewardOffer $rewardOffer,
        DeleteRewardOfferAction $action,
    ): RedirectResponse {
        Gate::authorize('loyalty-reward.delete');

        $action->execute($rewardOffer);

        return redirect()
            ->route('admin.reward-offers.index')
            ->with('success', 'پاداش با موفقیت حذف شد.');
    }

    /** @return array<string, mixed> */
    private function formData(RewardOfferConfigCatalog $catalog, ?RewardOffer $rewardOffer = null): array
    {
        $types = collect(RewardTypeEnum::cases())
            ->mapWithKeys(fn(RewardTypeEnum $type) => [$type->value => $type->label()])
            ->all();

        $couponTypes = collect(CouponTypeEnum::cases())
            ->mapWithKeys(fn(CouponTypeEnum $type) => [$type->value => $type->label()])
            ->all();

        $defaultConfigs = collect(RewardTypeEnum::cases())
            ->mapWithKeys(fn(RewardTypeEnum $type) => [
                $type->value => $catalog->defaultConfig($type),
            ])
            ->all();

        $raffleCampaignOptions = DrawCampaign::query()
            ->latest('id')
            ->limit(250)
            ->get(['id', 'title', 'status'])
            ->mapWithKeys(fn(DrawCampaign $campaign) => [
                $campaign->id => $campaign->title . ' — ' . $campaign->status->label(),
            ])
            ->all();

        if (
            $rewardOffer?->target instanceof DrawCampaign
            && !array_key_exists($rewardOffer->target->getKey(), $raffleCampaignOptions)
        ) {
            $raffleCampaignOptions = [
                $rewardOffer->target->getKey() => $rewardOffer->target->title . ' — ' . $rewardOffer->target->status->label(),
            ] + $raffleCampaignOptions;
        }

        return compact(
            'types',
            'couponTypes',
            'defaultConfigs',
            'raffleCampaignOptions',
        );
    }
}
