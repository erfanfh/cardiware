<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Application\Loyalty\Actions\ResolveUserLevelAction;
use App\Domain\Loyalty\Models\LoyaltyLevel;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LoyaltyLevelController extends Controller
{
    public function __construct(
        private readonly ResolveUserLevelAction $resolveUserLevel,
    ) {
    }

    public function index(): View
    {
        Gate::authorize('loyalty-rule.list');

        $levels = LoyaltyLevel::query()->orderBy('min_xp')->get();

        // تعداد اعضای هر سطح از روی بازه XP محاسبه می‌شود (رابطه مستقیم وجود ندارد).
        $levels->each(function (LoyaltyLevel $level, int $index) use ($levels): void {
            $next = $levels->slice($index + 1)->first();

            $level->members_count = \App\Domain\Loyalty\Models\PointAccount::query()
                ->where('lifetime_xp', '>=', $level->min_xp)
                ->when($next, fn ($query) => $query->where('lifetime_xp', '<', $next->min_xp))
                ->count();
        });

        return view('admin.loyalty.levels.index', compact('levels'));
    }

    public function create(): View
    {
        Gate::authorize('loyalty-rule.create');

        return view('admin.loyalty.levels.form', [
            'level' => new LoyaltyLevel([
                'purchase_multiplier' => 1,
                'color_key' => 'brand',
                'is_active' => true,
                'sort_order' => (int) LoyaltyLevel::query()->max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('loyalty-rule.create');

        $validated = $this->validated($request);

        LoyaltyLevel::query()->create($validated);
        $this->resolveUserLevel->forgetCache();

        return redirect()
            ->route('admin.loyalty-levels.index')
            ->with('success', 'سطح باشگاه با موفقیت ایجاد شد.');
    }

    public function edit(LoyaltyLevel $loyaltyLevel): View
    {
        Gate::authorize('loyalty-rule.list');

        return view('admin.loyalty.levels.form', ['level' => $loyaltyLevel]);
    }

    public function update(Request $request, LoyaltyLevel $loyaltyLevel): RedirectResponse
    {
        Gate::authorize('loyalty-rule.list');

        $loyaltyLevel->update($this->validated($request, $loyaltyLevel));
        $this->resolveUserLevel->forgetCache();

        return redirect()
            ->route('admin.loyalty-levels.index')
            ->with('success', 'سطح باشگاه با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(LoyaltyLevel $loyaltyLevel): RedirectResponse
    {
        Gate::authorize('loyalty-rule.delete');

        $loyaltyLevel->delete();
        $this->resolveUserLevel->forgetCache();

        return redirect()
            ->route('admin.loyalty-levels.index')
            ->with('success', 'سطح باشگاه حذف شد.');
    }

    private function validated(Request $request, ?LoyaltyLevel $ignore = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'badge' => ['nullable', 'string', 'max:16'],
            'min_xp' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'reward_points' => ['required', 'integer', 'min:0', 'max:1000000'],
            'purchase_multiplier' => ['required', 'numeric', 'min:1', 'max:5'],
            'color_key' => ['required', 'in:brand,slate,emerald,cyan,amber,violet,rose'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ], [
            'title.required' => 'عنوان سطح الزامی است.',
            'min_xp.required' => 'حداقل XP الزامی است.',
            'purchase_multiplier.max' => 'ضریب خرید حداکثر ۵ برابر باشد.',
            'color_key.in' => 'رنگ انتخابی معتبر نیست.',
        ]);
    }
}
