<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Application\Marketplace\SellerRewards\GetSellerRankingAction;
use App\Domain\Marketplace\Models\SellerPeriodClosure;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreSpotlight;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Setting\Groups\SellerRewardSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;

/**
 * برترین فروشندگان: رقابت جاری، برندگان دوره‌های قبل، جایگاه «فروشگاه ویژه» صفحه
 * اصلی (خودکار یا دستی) و تنظیمات جوایز.
 */
final class SellerRewardController extends Controller
{
    public function index(GetSellerRankingAction $ranking, SellerRewardSettings $settings): View
    {
        Gate::authorize('store.list');

        $live = collect(['week', 'month', 'year'])->mapWithKeys(fn (string $type) => [
            $type => ['period' => LeaderboardPeriod::make($type), 'rows' => $ranking->execute(LeaderboardPeriod::make($type), 5)],
        ]);

        return view('admin.marketplace.seller-rewards.index', [
            'live' => $live,
            'closures' => SellerPeriodClosure::query()->with('rewards.store')->latest('ends_at')->paginate(15),
            'spotlights' => StoreSpotlight::query()->where('ends_at', '>', now())->with('store')->orderBy('starts_at')->get(),
            'stores' => Store::query()->orderBy('title')->pluck('title', 'id'),
            'prizes' => collect(SellerRewardSettings::PERIODS)->mapWithKeys(fn ($label, $type) => [$type => $settings->prizes($type)]),
            'minNetSales' => collect(SellerRewardSettings::PERIODS)->mapWithKeys(fn ($label, $type) => [$type => $settings->minNetSales($type)]),
            'enabled' => $settings->enabled(),
            'spotlightTitle' => $settings->get('spotlight_title', 'فروشگاه ویژه'),
            'excludedStoreIds' => $settings->excludedStoreIds(),
        ]);
    }

    public function updateSettings(Request $request, SellerRewardSettings $settings): RedirectResponse
    {
        Gate::authorize('store.list');

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'spotlight_title' => ['nullable', 'string', 'max:80'],
            'excluded_store_ids' => ['nullable', 'array'],
            'excluded_store_ids.*' => ['integer', 'exists:stores,id'],
            'prizes' => ['required', 'array'],
            'prizes.*' => ['array', 'size:3'],
            'prizes.*.*.commission_bonus_bp' => ['required', 'integer', 'min:0', 'max:5000'],
            'prizes.*.*.bonus_days' => ['required', 'integer', 'min:0', 'max:365'],
            'prizes.*.*.coupon_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'prizes.*.*.coupon_max_amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'prizes.*.*.coupon_days' => ['required', 'integer', 'min:1', 'max:365'],
            'prizes.*.*.spotlight_days' => ['required', 'integer', 'min:0', 'max:90'],
            'min_net_sales' => ['required', 'array'],
            'min_net_sales.*' => ['required', 'integer', 'min:0', 'max:100000000000'],
        ]);

        $settings->set('enabled', (bool) $data['enabled']);
        $settings->set('spotlight_title', (string) ($data['spotlight_title'] ?? ''));
        $settings->set('excluded_store_ids', array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            array_filter($data['excluded_store_ids'] ?? [], static fn ($id): bool => (int) $id > 0),
        ))));
        $settings->set('prizes', array_intersect_key($data['prizes'], SellerRewardSettings::PERIODS));
        $settings->set('min_net_sales', array_intersect_key($data['min_net_sales'], SellerRewardSettings::PERIODS));

        return back()->with('success', 'تنظیمات جوایز فروشندگان ذخیره شد.');
    }

    public function storeSpotlight(Request $request): RedirectResponse
    {
        Gate::authorize('store.list');

        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'days' => ['required', 'integer', 'min:1', 'max:90'],
            'headline' => ['nullable', 'string', 'max:120'],
            'starts_now' => ['nullable', 'boolean'],
        ]);

        // انتخاب مدیر بر جایزه‌ی خودکار مقدم است و از همین حالا نمایش داده می‌شود.
        StoreSpotlight::query()->create([
            'store_id' => $data['store_id'],
            'source' => 'admin',
            'headline' => $data['headline'] ?? null,
            'starts_at' => now(),
            'ends_at' => now()->addDays((int) $data['days']),
            'created_by_user_id' => $request->user()->getKey(),
        ]);

        \Illuminate\Support\Facades\Cache::forget('home:featured-store-spotlight');

        return back()->with('success', 'فروشگاه ویژه‌ی صفحه اصلی ثبت شد.');
    }

    public function destroySpotlight(StoreSpotlight $spotlight): RedirectResponse
    {
        Gate::authorize('store.list');
        $spotlight->delete();
        \Illuminate\Support\Facades\Cache::forget('home:featured-store-spotlight');

        return back()->with('success', 'نمایش فروشگاه در صفحه اصلی حذف شد.');
    }

    public function closeFinished(): RedirectResponse
    {
        Gate::authorize('store.list');
        Artisan::call('marketplace:close-seller-periods');

        return back()->with('success', 'دوره‌های تمام‌شده بررسی شدند. ' . trim(Artisan::output()));
    }
}
