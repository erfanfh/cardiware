<?php

namespace App\Http\Controllers\Admin\Vip;

use App\Domain\Vip\Models\VipPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vip\SaveVipPlanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VipPlanController extends Controller
{
    public function index(): View
    {
        Gate::authorize('vip-plan.list');

        return view('admin.vip.plans.index', [
            'plans' => VipPlan::query()->orderBy('sort_order')->orderBy('id')->paginate(20),
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

        $vipPlan->update($request->validated());

        return back()->with('success', 'پلن VIP به‌روزرسانی شد.');
    }

    public function destroy(VipPlan $vipPlan): RedirectResponse
    {
        Gate::authorize('vip-plan.delete');

        $vipPlan->delete();

        return redirect()->route('admin.vip-plans.index')->with('success', 'پلن VIP حذف شد.');
    }
}
