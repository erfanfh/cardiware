<?php

namespace App\Http\Controllers\Admin\Vip;

use App\Application\Vip\Listings\VipPlanPurchaseListing;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vip\IndexVipPlanPurchaseRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VipPlanPurchaseController extends Controller
{
    public function __invoke(
        IndexVipPlanPurchaseRequest $request,
        VipPlan $vipPlan,
        VipPlanPurchaseListing $listing,
    ): View {
        Gate::authorize('vip-plan.list');

        $filters = $request->validated();
        $query = VipSubscription::query()
            ->where('vip_plan_id', $vipPlan->getKey())
            ->with(['user' => fn ($query) => $query->withTrashed(), 'order']);

        return view('admin.vip.plans.purchases.index', [
            'plan' => $vipPlan,
            'purchases' => $listing->handle($query, $filters)
                ->paginate($listing->perPage())->withQueryString(),
            'totalPurchases' => $vipPlan->subscriptions()->count(),
            'hasActiveFilters' => $listing->hasActiveFilters($filters),
        ]);
    }
}
