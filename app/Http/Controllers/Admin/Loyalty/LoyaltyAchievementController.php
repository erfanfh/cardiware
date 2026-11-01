<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Domain\Loyalty\Enums\AchievementMetricEnum;
use App\Domain\Loyalty\Models\LoyaltyAchievement;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LoyaltyAchievementController extends Controller
{
    public function index(): View
    {
        Gate::authorize('loyalty-rule.list');

        $achievements = LoyaltyAchievement::query()
            ->orderBy('sort_order')
            ->withCount('users as holders_count')
            ->get();

        return view('admin.loyalty.achievements.index', compact('achievements'));
    }

    public function create(): View
    {
        Gate::authorize('loyalty-rule.create');

        return view('admin.loyalty.achievements.form', [
            'achievement' => new LoyaltyAchievement([
                'metric' => AchievementMetricEnum::PAID_ORDERS_COUNT,
                'threshold' => 1,
                'reward_points' => 0,
                'is_active' => true,
                'sort_order' => (int) LoyaltyAchievement::query()->max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('loyalty-rule.create');

        LoyaltyAchievement::query()->create($this->validated($request));

        return redirect()->route('admin.loyalty-achievements.index')
            ->with('success', 'نشان با موفقیت ایجاد شد.');
    }

    public function edit(LoyaltyAchievement $loyaltyAchievement): View
    {
        Gate::authorize('loyalty-rule.list');

        return view('admin.loyalty.achievements.form', ['achievement' => $loyaltyAchievement]);
    }

    public function update(Request $request, LoyaltyAchievement $loyaltyAchievement): RedirectResponse
    {
        Gate::authorize('loyalty-rule.list');

        $loyaltyAchievement->update($this->validated($request));

        return redirect()->route('admin.loyalty-achievements.index')
            ->with('success', 'نشان با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(LoyaltyAchievement $loyaltyAchievement): RedirectResponse
    {
        Gate::authorize('loyalty-rule.delete');

        $loyaltyAchievement->delete();

        return redirect()->route('admin.loyalty-achievements.index')
            ->with('success', 'نشان حذف شد.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'badge' => ['nullable', 'string', 'max:16'],
            'metric' => ['required', 'in:' . collect(AchievementMetricEnum::cases())->pluck('value')->implode(',')],
            'threshold' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'reward_points' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ], [
            'code.required' => 'کد نشان الزامی است.',
            'metric.in' => 'معیار انتخابی معتبر نیست.',
        ]);
    }
}
