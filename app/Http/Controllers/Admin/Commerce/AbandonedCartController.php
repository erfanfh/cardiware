<?php

namespace App\Http\Controllers\Admin\Commerce;

use App\Application\Commerce\Actions\Cart\CleanupStaleActiveCartsAction;
use App\Application\Commerce\Listings\AbandonedCartListing;
use App\Domain\Commerce\Models\Cart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Commerce\IndexAbandonedCartRequest;
use App\Http\Requests\Admin\Commerce\UpdateAbandonedCartSettingsRequest;
use App\Infrastructure\Services\Setting\Groups\CommerceSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class AbandonedCartController extends Controller
{
    public function index(
        IndexAbandonedCartRequest $request,
        AbandonedCartListing $abandonedCartListing,
        CleanupStaleActiveCartsAction $cleanupStaleActiveCartsAction,
    ): View {
        Gate::authorize('cart.abandoned');

        $validated = $request->validated();

        $guestRetentionDays = $cleanupStaleActiveCartsAction->guestRetentionDays();
        $userRetentionMonths = $cleanupStaleActiveCartsAction->userRetentionMonths();
        $guestCleanupCutoff = $cleanupStaleActiveCartsAction->guestCutoff($guestRetentionDays);
        $userCleanupCutoff = $cleanupStaleActiveCartsAction->userCutoff($userRetentionMonths);

        $baseQuery = Cart::query()
            ->active()
            ->whereHas('items');

        $carts = $abandonedCartListing
            ->handle(
                (clone $baseQuery)
                    ->with([
                        'user' => fn ($query) => $query->withTrashed(),
                        'rootItems.children',
                    ])
                    ->withCount('items'),
                $validated,
            )
            ->paginate($abandonedCartListing->perPage())
            ->withQueryString();

        $guestReadyForCleanup = Cart::query()
            ->active()
            ->whereNull('user_id')
            ->where('updated_at', '<=', $guestCleanupCutoff)
            ->count();

        $userReadyForCleanup = Cart::query()
            ->active()
            ->whereNotNull('user_id')
            ->where('updated_at', '<=', $userCleanupCutoff)
            ->count();

        $stats = [
            'count' => (clone $baseQuery)->count(),
            'guest_count' => (clone $baseQuery)->whereNull('user_id')->count(),
            'user_count' => (clone $baseQuery)->whereNotNull('user_id')->count(),
            'total_amount' => (int) (clone $baseQuery)->sum('total_amount'),
            'guest_ready_for_cleanup' => $guestReadyForCleanup,
            'user_ready_for_cleanup' => $userReadyForCleanup,
            'ready_for_cleanup' => $guestReadyForCleanup + $userReadyForCleanup,
        ];

        $hasActiveFilters = $abandonedCartListing->hasActiveFilters($validated);

        return view('admin.commerce.abandoned-carts.index', compact(
            'carts',
            'stats',
            'guestRetentionDays',
            'userRetentionMonths',
            'guestCleanupCutoff',
            'userCleanupCutoff',
            'hasActiveFilters',
        ));
    }

    public function updateSettings(
        UpdateAbandonedCartSettingsRequest $request,
        CommerceSettings $commerceSettings,
    ): RedirectResponse {
        Gate::authorize('cart.abandoned');

        $validated = $request->validated();

        try {
            DB::transaction(function () use ($commerceSettings, $validated): void {
                $commerceSettings->set(
                    'abandoned_guest_cart_retention_days',
                    (int) $validated['guest_retention_days'],
                );

                $commerceSettings->set(
                    'abandoned_user_cart_retention_months',
                    (int) $validated['user_retention_months'],
                );
            });

            return redirect()
                ->route('admin.abandoned-carts.index')
                ->with('success', 'تنظیمات پاکسازی سبدهای خرید با موفقیت ذخیره شد.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.abandoned-carts.index')
                ->withInput()
                ->with('error', 'ذخیره تنظیمات انجام نشد. لطفا دوباره تلاش کنید.');
        }
    }

    public function cleanupNow(
        CleanupStaleActiveCartsAction $cleanupStaleActiveCartsAction,
    ): RedirectResponse {
        Gate::authorize('cart.abandoned');

        try {
            $deleted = $cleanupStaleActiveCartsAction->execute();

            if ($deleted['total'] === 0) {
                return redirect()
                    ->route('admin.abandoned-carts.index')
                    ->with('success', 'سبد خریدی با شرایط پاکسازی پیدا نشد.');
            }

            return redirect()
                ->route('admin.abandoned-carts.index')
                ->with(
                    'success',
                    "{$deleted['total']} سبد خرید پاک شد؛ {$deleted['guest']} سبد مهمان و {$deleted['user']} سبد کاربر عضو.",
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.abandoned-carts.index')
                ->with('error', 'در پاکسازی سبدهای خرید خطایی رخ داد. لطفا دوباره تلاش کنید.');
        }
    }
}
