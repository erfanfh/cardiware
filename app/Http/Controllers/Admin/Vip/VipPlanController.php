<?php

namespace App\Http\Controllers\Admin\Vip;

use App\Application\Vip\Listings\VipPlanListing;
use App\Domain\Vip\Models\VipPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vip\IndexVipPlanRequest;
use App\Http\Requests\Admin\Vip\SaveVipPlanRequest;
use App\Infrastructure\Services\Setting\Groups\VipSettings;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VipPlanController extends Controller
{
    public function index(IndexVipPlanRequest $request, VipPlanListing $listing): View
    {
        Gate::authorize('vip-plan.list');

        $filters = $request->validated();
        $allowedActions = [];

        if (Gate::allows('vip-plan.edit')) {
            $allowedActions = ['activate', 'deactivate'];
        }

        if (Gate::allows('vip-plan.delete')) {
            $allowedActions[] = 'delete';
        }

        return view('admin.vip.plans.index', [
            'plans' => $listing->handle(VipPlan::query(), $filters)
                ->paginate($listing->perPage())->withQueryString(),
            'hasActiveFilters' => $listing->hasActiveFilters($filters),
            'bulkActionOptions' => DefaultBulkActions::set()->only($allowedActions)->toOptions(),
            'hasBulkActions' => $allowedActions !== [],
            'featuredPlanId' => app(VipSettings::class)->get('featured_plan_id'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('vip-plan.create');

        return view('admin.vip.plans.create');
    }

    public function store(SaveVipPlanRequest $request): RedirectResponse
    {
        Gate::authorize('vip-plan.create');

        $plan = VipPlan::query()->create($request->validated());

        return redirect()->route('admin.vip-plans.edit', $plan)->with('success', 'پلن VIP ایجاد شد.');
    }

    public function edit(VipPlan $vipPlan): View
    {
        Gate::authorize('vip-plan.edit');

        return view('admin.vip.plans.edit', ['plan' => $vipPlan]);
    }

    public function update(SaveVipPlanRequest $request, VipPlan $vipPlan): RedirectResponse
    {
        Gate::authorize('vip-plan.edit');

        $vipPlan->update(Arr::except($request->validated(), ['code']));

        return back()->with('success', 'پلن VIP به‌روزرسانی شد.');
    }

    public function destroy(VipPlan $vipPlan): RedirectResponse
    {
        Gate::authorize('vip-plan.delete');

        $vipPlan->delete();

        return redirect()->route('admin.vip-plans.index')->with('success', 'پلن VIP حذف شد.');
    }
}
