<?php

namespace App\Http\Controllers\Admin\Affiliate;

use App\Application\Affiliate\Actions\GetAffiliateOverviewAction;
use App\Domain\Affiliate\Enums\AffiliateCommissionTypeEnum;
use App\Domain\Affiliate\Models\Affiliate;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('affiliate.list');

        $search = trim((string) $request->query('search'));

        $affiliates = Affiliate::query()
            ->with('user:id,first_name,last_name,email,mobile')
            ->withCount('orders as orders_count')
            ->withSum(['orders as active_commission' => fn ($q) => $q->where('status', 'active')], 'commission_amount')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.affiliate.index', compact('affiliates', 'search'));
    }

    public function create(): View
    {
        Gate::authorize('affiliate.create');

        return view('admin.affiliate.form', [
            'affiliate' => new Affiliate([
                'is_active' => true,
                'commission_type' => AffiliateCommissionTypeEnum::PERCENT,
                'commission_value' => 25,
                'cookie_days' => 30,
                'attribution_days' => 90,
                'min_withdrawal_amount' => 100000,
            ]),
            'userId' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('affiliate.create');

        $validated = $this->validated($request, requireUser: true);

        $user = User::query()->findOrFail((int) ($validated['user_id'] ?? 0));

        if (Affiliate::query()->where('user_id', $user->getKey())->exists()) {
            return back()->with('error', 'این کاربر قبلا همکار شده است.');
        }

        Affiliate::query()->create($validated);

        return redirect()->route('admin.affiliates.index')
            ->with('success', 'همکار در فروش با موفقیت ایجاد شد.');
    }

    public function show(Affiliate $affiliate, GetAffiliateOverviewAction $overviewAction): View
    {
        Gate::authorize('affiliate.list');

        $overview = $overviewAction->execute($affiliate);
        $affiliate->load('user');

        return view('admin.affiliate.show', [
            'affiliate' => $affiliate,
            'overview' => $overview,
        ]);
    }

    public function edit(Affiliate $affiliate): View
    {
        Gate::authorize('affiliate.edit');

        return view('admin.affiliate.form', [
            'affiliate' => $affiliate,
            'userId' => $affiliate->user_id,
        ]);
    }

    public function update(Request $request, Affiliate $affiliate): RedirectResponse
    {
        Gate::authorize('affiliate.edit');

        $validated = $this->validated($request, requireUser: false);
        unset($validated['user_id']);

        $affiliate->update($validated);

        return redirect()->route('admin.affiliates.show', $affiliate)
            ->with('success', 'تنظیمات همکار با موفقیت ذخیره شد.');
    }

    public function destroy(Affiliate $affiliate): RedirectResponse
    {
        Gate::authorize('affiliate.delete');

        $affiliate->delete();

        return redirect()->route('admin.affiliates.index')
            ->with('success', 'همکار حذف شد؛ سابقه کمیسیون و برداشت‌های او نیز حذف شد.');
    }

    private function validated(Request $request, bool $requireUser): array
    {
        return $request->validate([
            'user_id' => [$requireUser ? 'required' : 'nullable', 'integer', 'exists:users,id'],
            'code' => [
                'required', 'string', 'min:3', 'max:64',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],
            'is_active' => ['required', 'boolean'],
            'commission_type' => ['required', 'in:' . collect(AffiliateCommissionTypeEnum::cases())->pluck('value')->implode(',')],
            'commission_value' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'cookie_days' => ['required', 'integer', 'min:1', 'max:365'],
            'attribution_days' => ['required', 'integer', 'min:1', 'max:365'],
            'min_withdrawal_amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'code.regex' => 'کد همکار فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',
            'code.required' => 'کد همکار الزامی است.',
            'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
        ]);
    }
}
