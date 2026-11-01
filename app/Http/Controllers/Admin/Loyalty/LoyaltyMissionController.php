<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Domain\Loyalty\Enums\MissionMetricEnum;
use App\Domain\Loyalty\Models\LoyaltyMission;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LoyaltyMissionController extends Controller
{
    public function index(): View
    {
        Gate::authorize('loyalty-rule.list');

        $missions = LoyaltyMission::query()
            ->orderBy('sort_order')
            ->withCount('progressEntries as completions_count')
            ->get();

        return view('admin.loyalty.missions.index', compact('missions'));
    }

    public function create(): View
    {
        Gate::authorize('loyalty-rule.create');

        return view('admin.loyalty.missions.form', [
            'mission' => new LoyaltyMission([
                'metric' => MissionMetricEnum::DAILY_VISITS,
                'target_count' => 3,
                'reward_points' => 100,
                'is_active' => true,
                'sort_order' => (int) LoyaltyMission::query()->max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('loyalty-rule.create');

        LoyaltyMission::query()->create($this->validated($request));

        return redirect()->route('admin.loyalty-missions.index')
            ->with('success', 'مأموریت با موفقیت ایجاد شد.');
    }

    public function edit(LoyaltyMission $loyaltyMission): View
    {
        Gate::authorize('loyalty-rule.list');

        return view('admin.loyalty.missions.form', ['mission' => $loyaltyMission]);
    }

    public function update(Request $request, LoyaltyMission $loyaltyMission): RedirectResponse
    {
        Gate::authorize('loyalty-rule.list');

        $loyaltyMission->update($this->validated($request));

        return redirect()->route('admin.loyalty-missions.index')
            ->with('success', 'مأموریت با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(LoyaltyMission $loyaltyMission): RedirectResponse
    {
        Gate::authorize('loyalty-rule.delete');

        $loyaltyMission->delete();

        return redirect()->route('admin.loyalty-missions.index')
            ->with('success', 'مأموریت حذف شد.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:16'],
            'metric' => ['required', 'in:' . collect(MissionMetricEnum::cases())->pluck('value')->implode(',')],
            'target_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'reward_points' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ], [
            'code.required' => 'کد مأموریت الزامی است.',
            'metric.in' => 'معیار انتخابی معتبر نیست.',
        ]);
    }
}
